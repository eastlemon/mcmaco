<?php

namespace Tests\Feature;

use App\Jobs\RunPipelineJob;
use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Models\User;
use App\Pipelines\PipelineRunFailed;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A failed pipeline run must produce exactly ONE failed log entry with a
 * readable message. Previously PipelineService recorded the failure and
 * rethrew, then RunPipelineJob::failed() wrote a duplicate "Job failed:"
 * entry — and a missing CSV source reported an empty path
 * ("CSV file not found: ").
 */
class RunPipelineFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_missing_csv_source_reports_clear_message(): void
    {
        // Like a pipeline saved through the broken form: no csv_file, no file_path.
        $pipeline = Pipeline::create([
            'name' => 'No source',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => [],
            'is_active' => true,
        ]);

        try {
            app(PipelineService::class)->run($pipeline);
        } catch (PipelineRunFailed $e) {
            $this->assertStringContainsString('not configured', $e->getMessage());
        }

        $log = $pipeline->logs()->first();
        $this->assertNotNull($log);
        $this->assertSame(PipelineLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('not configured', $log->message);
        $this->assertSame(1, $pipeline->logs()->count());
    }

    public function test_job_failed_hook_does_not_duplicate_service_failure(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Dedupe',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => ['file_path' => '/nonexistent/file.csv'],
            'is_active' => true,
        ]);

        try {
            app(PipelineService::class)->run($pipeline);
            $this->fail('PipelineService::run() must rethrow a failed import');
        } catch (PipelineRunFailed $e) {
            // Queue worker calls the failed() hook with the same exception.
            (new RunPipelineJob($pipeline))->failed($e);
        }

        $this->assertSame(1, $pipeline->logs()->count());
        $this->assertSame(
            PipelineLog::STATUS_FAILED,
            $pipeline->logs()->first()->status,
        );
        $this->assertStringContainsString('CSV file not found: /nonexistent/file.csv', $pipeline->logs()->first()->message);
    }

    public function test_job_failed_hook_recovers_stuck_running_log(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Timeout',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => [],
            'is_active' => true,
        ]);

        // Simulate a worker kill mid-run: the log entry stays "running".
        $log = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_RUNNING,
            'message' => 'Запуск: Timeout',
        ]);

        (new RunPipelineJob($pipeline))->failed(new \RuntimeException('Maximum execution time exceeded'));

        $log->refresh();
        $this->assertSame(PipelineLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('Maximum execution time exceeded', $log->message);
        $this->assertSame(1, $pipeline->logs()->count());
    }

    public function test_job_failed_hook_creates_log_when_service_never_ran(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Cold',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => [],
            'is_active' => true,
        ]);

        $this->assertSame(0, $pipeline->logs()->count());

        (new RunPipelineJob($pipeline))->failed(new \RuntimeException('Connection refused'));

        $log = $pipeline->logs()->first();
        $this->assertSame(PipelineLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('Connection refused', $log->message);
    }
}