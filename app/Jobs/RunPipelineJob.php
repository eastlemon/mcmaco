<?php

namespace App\Jobs;

use App\Models\Pipeline;
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

        $this->pipeline->logs()->create([
            'status' => 'failed',
            'message' => "Job failed: {$exception->getMessage()}",
            'errors' => 1,
        ]);
    }
}