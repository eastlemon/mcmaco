<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Models\User;
use App\Pipelines\Adapters\OrdersExport;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PipelineServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
    }

    public function test_service_runs_import_pipeline_and_logs_success(): void
    {
        // Create a CSV file
        $dir = storage_path('app/imports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = "{$dir}/test_import.csv";
        file_put_contents($path, "title,sku,price,stock\nService Test,SV-001,100,2\n");

        $pipeline = Pipeline::create([
            'name' => 'Test Import',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => [
                'file_path' => $path,
                'delimiter' => ',',
                'default_user_id' => 1,
            ],
            'is_active' => true,
        ]);

        $service = app(PipelineService::class);
        $log = $service->run($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->status);
        $this->assertSame(1, $log->processed);
        $this->assertSame(1, $log->created);
        $this->assertSame(0, $log->errors);
        $this->assertDatabaseHas('ads', ['sku' => 'SV-001', 'title' => 'Service Test']);

        // Cleanup
        unlink($path);
    }

    public function test_service_logs_failure_on_bad_config(): void
    {
        $pipeline = Pipeline::create([
            'name' => 'Bad Import',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => [
                'file_path' => '/nonexistent/file.csv',
                'delimiter' => ',',
            ],
            'is_active' => true,
        ]);

        $service = app(PipelineService::class);

        try {
            $service->run($pipeline);
        } catch (\Throwable) {
            // Service rethrows, but log should already be saved
        }

        $log = $pipeline->logs()->first();
        $this->assertNotNull($log);
        $this->assertSame(PipelineLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('not found', $log->message);
    }

    public function test_service_runs_export_pipeline(): void
    {
        // Create an order to export
        Order::factory()->create([
            'customer_name' => 'Export Test',
            'status' => 'new',
        ]);

        $pipeline = Pipeline::create([
            'name' => 'Orders Export',
            'type' => Pipeline::TYPE_EXPORT,
            'adapter' => 'orders_export',
            'format' => 'csv',
            'config' => [
                'days_back' => 30,
                'delimiter' => ';',
            ],
            'is_active' => true,
        ]);

        $service = app(PipelineService::class);
        $log = $service->run($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->status);
        $this->assertGreaterThanOrEqual(1, $log->processed);
    }

    public function test_import_pipeline_updates_existing_product(): void
    {
        $dir = storage_path('app/imports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = "{$dir}/test_update.csv";

        // First import — create
        file_put_contents($path, "title,sku,price,stock\nFirst,UPD-SV,100,1\n");
        $pipeline = Pipeline::create([
            'name' => 'Update Test',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => ['file_path' => $path, 'delimiter' => ',', 'default_user_id' => 1],
            'is_active' => true,
        ]);

        $service = app(PipelineService::class);
        $service->run($pipeline->fresh());

        // Second import — update same SKU
        file_put_contents($path, "title,sku,price,stock\nUpdated,UPD-SV,200,5\n");
        $log2 = $service->run($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log2->status);
        $this->assertSame(1, $log2->processed);
        $this->assertSame(1, $log2->updated);
        $this->assertSame(0, $log2->created);

        $ad = Ad::where('sku', 'UPD-SV')->first();
        $this->assertSame('Updated', $ad->title);
        $this->assertSame(200, $ad->price);

        unlink($path);
    }
}
