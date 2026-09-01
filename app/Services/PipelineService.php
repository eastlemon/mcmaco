<?php

namespace App\Services;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Pipelines\AdapterRegistry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PipelineService
{
    /** Hard cap on rows per single import run. */
    public const MAX_ROWS_PER_RUN = 20000;

    public function __construct(
        private AdapterRegistry $registry,
    ) {}

    /**
     * Run a pipeline: imports go through the batched queue flow,
     * exports are processed inline.
     */
    public function run(Pipeline $pipeline): PipelineLog
    {
        if ($pipeline->type === Pipeline::TYPE_IMPORT) {
            return $this->runImport($pipeline);
        }

        $log = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_RUNNING,
            'message' => 'Запуск: ' . $pipeline->name,
        ]);

        try {
            $adapter = $this->registry->getAdapter($pipeline->adapter, $pipeline->type);
            $result = $this->runExport($adapter, $pipeline->config ?? []);

            $log->update([
                'status' => PipelineLog::STATUS_SUCCESS,
                'message' => 'Готово: ' . ($result['processed'] ?? 0) . ' обработано',
                'processed' => $result['processed'] ?? 0,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => PipelineLog::STATUS_FAILED,
                'message' => "Ошибка: {$e->getMessage()}",
                'errors' => 1,
            ]);

            throw $e;
        }

        return $log->refresh();
    }

    /**
     * Batched import: read the source, extract the photo archive (if any),
     * fan out one queued job per row and finalize with a summary log entry.
     */
    public function runImport(Pipeline $pipeline): PipelineLog
    {
        $log = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_RUNNING,
            'message' => 'Запуск: ' . $pipeline->name,
        ]);

        $runDir = null;

        try {
            $config = $pipeline->config ?? [];
            $adapter = $this->registry->getImportAdapter($pipeline->adapter, $pipeline->type);
            $rows = iterator_to_array($adapter->read($config));

            if (count($rows) > self::MAX_ROWS_PER_RUN) {
                $rows = array_slice($rows, 0, self::MAX_ROWS_PER_RUN);
            }

            $config = $this->preparePhotos($config, $runDir);

            if ($rows === []) {
                $this->cleanupRunDir($runDir);

                $log->update([
                    'status' => PipelineLog::STATUS_FAILED,
                    'message' => 'Ошибка: CSV не содержит строк',
                    'errors' => 1,
                ]);

                return $log->refresh();
            }

            $jobs = [];

            foreach ($rows as $row) {
                $jobs[] = new \App\Jobs\ImportProductRowJob($pipeline->id, $row, $config, $log->id);
            }

            $pipelineId = $pipeline->id;
            $logId = $log->id;

            $batch = Bus::batch($jobs)
                ->name("Импорт: {$pipeline->name}")
                ->finally(fn () => \App\Jobs\FinalizeImportRunJob::dispatch($pipelineId, $logId, $runDir))
                ->dispatch();

            $message = 'В очереди: ' . count($rows) . ' строк';

            if (isset($config['_photos_extracted'])) {
                $message .= ', фото распаковано: ' . $config['_photos_extracted'];
            }

            $log->update([
                'message' => $message,
                'details' => array_merge($log->details ?? [], ['batch_id' => $batch->id]),
            ]);
        } catch (Throwable $e) {
            $this->cleanupRunDir($runDir);

            $log->update([
                'status' => PipelineLog::STATUS_FAILED,
                'message' => "Ошибка: {$e->getMessage()}",
                'errors' => 1,
            ]);

            throw $e;
        }

        return $log->refresh();
    }

    /**
     * Extract the photos archive (if configured) into a per-run temp directory
     * on the "local" disk and expose its absolute path via config['photos_dir'].
     *
     * @param string|null $runDir Relative run dir on the "local" disk (set by the method).
     */
    private function preparePhotos(array $config, ?string &$runDir): array
    {
        $zipRel = $config['photos_zip'] ?? null;

        if (!$zipRel) {
            return $config;
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($zipRel)) {
            throw new \RuntimeException("ZIP archive not found: {$zipRel}");
        }

        $runDir = 'pipeline-runs/' . uniqid('run_');
        $destAbs = $disk->path($runDir);

        $stats = app(ZipExtractor::class)->extract($disk->path($zipRel), $destAbs);

        // Normalize the photos root: the documented layout is photos/{SKU}/ inside
        // the archive, but a zip of the photos/ folder contents is accepted too.
        $photosRoot = is_dir($destAbs . '/photos') ? $destAbs . '/photos' : $destAbs;

        $config['photos_dir'] = $photosRoot;
        $config['_photos_extracted'] = $stats['files'];

        return $config;
    }

    private function cleanupRunDir(?string $runDir): void
    {
        if ($runDir) {
            Storage::disk('local')->deleteDirectory($runDir);
        }
    }

    /**
     * @param \App\Pipelines\Contracts\ExportAdapter $adapter
     */
    private function runExport($adapter, array $config): array
    {
        $items = $adapter->query($config);
        $adapter->write($items, $config);

        return [
            'processed' => $items->count(),
        ];
    }
}