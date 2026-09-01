<?php

namespace App\Jobs;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Pipelines\AdapterRegistry;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Processes a single CSV row of a batched import pipeline run.
 * Increments the run's PipelineLog counters atomically.
 */
class ImportProductRowJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(
        public int $pipelineId,
        public array $row,
        public array $config,
        public int $logId,
    ) {}

    public function handle(AdapterRegistry $registry): void
    {
        $pipeline = Pipeline::find($this->pipelineId);

        if (!$pipeline) {
            return;
        }

        try {
            $adapter = $registry->getImportAdapter($pipeline->adapter);
            $result = $adapter->process($this->row, $this->config);
        } catch (\Throwable $e) {
            $this->recordError($pipeline, "SKU " . ($this->row['sku'] ?? '?') . ": {$e->getMessage()}");

            throw $e;
        }

        PipelineLog::whereKey($this->logId)->increment('processed');

        if (($result['action'] ?? null) === 'created') {
            PipelineLog::whereKey($this->logId)->increment('created');
        } elseif (($result['action'] ?? null) === 'updated') {
            PipelineLog::whereKey($this->logId)->increment('updated');
        } elseif (($result['action'] ?? null) === 'error') {
            PipelineLog::whereKey($this->logId)->increment('errors');
            $this->recordError($pipeline, $result['message'] ?? 'row error');
        }

        if (($result['photos'] ?? 0) > 0) {
            PipelineLog::whereKey($this->logId)->increment('photos', (int) $result['photos']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $pipeline = Pipeline::find($this->pipelineId);

        if ($pipeline) {
            $this->recordError($pipeline, "SKU " . ($this->row['sku'] ?? '?') . ": {$exception->getMessage()}");
        }

        PipelineLog::whereKey($this->logId)->increment('errors');
    }

    /**
     * Keep per-row failures visible in the pipeline logs table.
     */
    private function recordError(Pipeline $pipeline, string $message): void
    {
        $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_FAILED,
            'message' => $message,
            'errors' => 1,
        ]);
    }
}