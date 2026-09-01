<?php

namespace App\Jobs;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Finalizes a batched import run: writes the summary log entry
 * and cleans up the temporary directory with extracted photos.
 */
class FinalizeImportRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(
        public int $pipelineId,
        public int $logId,
        public ?string $runDir = null,
    ) {}

    public function handle(): void
    {
        try {
            $log = PipelineLog::find($this->logId);

            if ($log && $log->status === PipelineLog::STATUS_RUNNING) {
                $hasProcessed = $log->processed > 0;
                $log->update([
                    'status' => $hasProcessed || $log->errors === 0
                        ? PipelineLog::STATUS_SUCCESS
                        : PipelineLog::STATUS_FAILED,
                    'message' => "Готово: {$log->processed} обработано, {$log->created} создано, "
                        . "{$log->updated} обновлено, {$log->errors} ошибок, {$log->photos} фото",
                ]);
            }
        } finally {
            if ($this->runDir) {
                Storage::disk('local')->deleteDirectory($this->runDir);
            }
        }
    }
}