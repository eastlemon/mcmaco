<?php

namespace App\Services;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Pipelines\AdapterRegistry;

class PipelineService
{
    public function __construct(
        private AdapterRegistry $registry,
    ) {}

    public function run(Pipeline $pipeline): PipelineLog
    {
        $log = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_RUNNING,
            'message' => 'Запуск: ' . $pipeline->name,
        ]);

        try {
            $adapter = $this->registry->getAdapter($pipeline->adapter, $pipeline->type);
            $stats = ['processed' => 0, 'created' => 0, 'updated' => 0, 'errors' => 0];

            if ($pipeline->type === Pipeline::TYPE_IMPORT) {
                $result = $this->runImport($adapter, $pipeline->config ?? []);
                $stats = array_merge($stats, $result);
            } elseif ($pipeline->type === Pipeline::TYPE_EXPORT) {
                $result = $this->runExport($adapter, $pipeline->config ?? []);
                $stats = array_merge($stats, $result);
            }

            $log->update([
                'status' => PipelineLog::STATUS_SUCCESS,
                'message' => "Готово: " . ($stats['processed'] ?? 0) . " обработано, "
                    . ($stats['created'] ?? 0) . " создано, "
                    . ($stats['updated'] ?? 0) . " обновлено, "
                    . ($stats['errors'] ?? 0) . " ошибок",
                'processed' => $stats['processed'] ?? 0,
                'created' => $stats['created'] ?? 0,
                'updated' => $stats['updated'] ?? 0,
                'errors' => $stats['errors'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => PipelineLog::STATUS_FAILED,
                'message' => "Ошибка: {$e->getMessage()}",
                'errors' => 1,
            ]);

            throw $e;
        }

        $log->refresh();

        return $log;
    }

    private function runImport($adapter, array $config): array
    {
        $stats = ['processed' => 0, 'created' => 0, 'updated' => 0, 'errors' => 0];

        foreach ($adapter->read($config) as $row) {
            $stats['processed']++;

            try {
                $result = $adapter->process($row, $config);

                if ($result['action'] === 'created') {
                    $stats['created']++;
                } elseif ($result['action'] === 'updated') {
                    $stats['updated']++;
                } elseif ($result['action'] === 'error') {
                    $stats['errors']++;
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
            }
        }

        return $stats;
    }

    private function runExport($adapter, array $config): array
    {
        $items = $adapter->query($config);
        $path = $adapter->write($items, $config);

        return [
            'processed' => $items->count(),
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];
    }
}