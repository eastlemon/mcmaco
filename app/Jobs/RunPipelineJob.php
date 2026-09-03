<?php

namespace App\Jobs;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Pipelines\PipelineRunFailed;
use App\Services\PipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunPipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public Pipeline $pipeline,
    ) {}

    public function handle(PipelineService $service): void
    {
        Log::info("Pipeline {$this->pipeline->id} ({$this->pipeline->name}): starting");

        $service->run($this->pipeline);

        Log::info("Pipeline {$this->pipeline->id}: completed");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Pipeline {$this->pipeline->id} failed: {$exception->getMessage()}");

        if ($exception instanceof PipelineRunFailed && $exception->log !== null) {
            // PipelineService already recorded the failure in its log.
            return;
        }

        // Recover a stuck "running" entry (timeout, worker kill) or
        // record a new entry for failures outside PipelineService.
        $log = $this->pipeline->logs()
            ->where('status', PipelineLog::STATUS_RUNNING)
            ->latest('id')
            ->first();

        $attributes = [
            'status' => PipelineLog::STATUS_FAILED,
            'message' => "Job failed: {$exception->getMessage()}",
            'errors' => 1,
        ];

        if ($log !== null) {
            $log->update($attributes);
        } else {
            $this->pipeline->logs()->create($attributes);
        }
    }
}