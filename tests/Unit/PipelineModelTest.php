<?php

namespace Tests\Unit;

use App\Models\Pipeline;
use App\Models\PipelineLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_can_be_created_with_defaults(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Test Import',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => Pipeline::FORMAT_CSV,
            'config' => ['file_path' => '/tmp/test.csv'],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('pipelines', ['id' => $pipeline->id]);
        $this->assertSame('import', $pipeline->type);
        $this->assertTrue($pipeline->is_active);
        $this->assertIsArray($pipeline->config);
        $this->assertSame('/tmp/test.csv', $pipeline->config['file_path']);
    }

    public function test_config_defaults_to_empty_array(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'No Config',
            'type' => Pipeline::TYPE_EXPORT,
            'adapter' => 'orders_export',
            'format' => 'csv',
        ]);

        $this->assertSame([], $pipeline->config);
    }

    public function test_logs_relationship(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'With Logs',
            'type' => 'import',
            'adapter' => 'csv_products',
            'format' => 'csv',
        ]);

        $log1 = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_SUCCESS,
            'message' => 'OK',
            'processed' => 10,
        ]);

        $log2 = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_FAILED,
            'message' => 'Error',
            'errors' => 1,
        ]);

        $this->assertCount(2, $pipeline->logs);
        // latest() scope — first() should be the most recent
        $this->assertTrue($pipeline->lastRun()->is($log2));
    }

    public function test_active_scope(): void
    {
        Pipeline::create(['name' => 'Active', 'type' => 'import', 'adapter' => 'csv_products', 'format' => 'csv', 'is_active' => true]);
        Pipeline::create(['name' => 'Inactive', 'type' => 'import', 'adapter' => 'csv_products', 'format' => 'csv', 'is_active' => false]);

        $active = Pipeline::active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('Active', $active->first()->name);
    }

    public function test_pipeline_log_fillable_and_casts(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Casts Test',
            'type' => 'import',
            'adapter' => 'csv_products',
            'format' => 'csv',
        ]);

        $log = $pipeline->logs()->create([
            'status' => PipelineLog::STATUS_SUCCESS,
            'message' => 'Done',
            'processed' => 5,
            'created' => 2,
            'updated' => 3,
            'errors' => 0,
            'details' => ['rows' => [1, 2, 3]],
        ]);

        $this->assertSame(5, $log->processed);
        $this->assertIsArray($log->details);
        $this->assertSame([1, 2, 3], $log->details['rows']);
    }
}
