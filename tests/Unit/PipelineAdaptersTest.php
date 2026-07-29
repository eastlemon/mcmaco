<?php

namespace Tests\Unit;

use App\Models\User;
use App\Pipelines\Adapters\CsvProductsImport;
use App\Pipelines\Adapters\OrdersExport;
use App\Pipelines\AdapterRegistry;
use App\Pipelines\Contracts\ExportAdapter;
use App\Pipelines\Contracts\ImportAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineAdaptersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create default user for imports
        User::factory()->create(['id' => 1]);
    }
    public function test_registry_lists_registered_adapters(): void
    {
        $registry = app(AdapterRegistry::class);

        $this->assertArrayHasKey('csv_products', $registry->listImports());
        $this->assertArrayHasKey('orders_export', $registry->listExports());
    }

    public function test_registry_resolves_import_adapter(): void
    {
        $registry = app(AdapterRegistry::class);
        $adapter = $registry->getAdapter('csv_products', 'import');

        $this->assertInstanceOf(ImportAdapter::class, $adapter);
        $this->assertInstanceOf(CsvProductsImport::class, $adapter);
    }

    public function test_registry_resolves_export_adapter(): void
    {
        $registry = app(AdapterRegistry::class);
        $adapter = $registry->getAdapter('orders_export', 'export');

        $this->assertInstanceOf(ExportAdapter::class, $adapter);
        $this->assertInstanceOf(OrdersExport::class, $adapter);
    }

    public function test_registry_throws_for_unknown_adapter(): void
    {
        $registry = app(AdapterRegistry::class);

        $this->expectException(\InvalidArgumentException::class);
        $registry->getAdapter('nonexistent', 'import');
    }

    public function test_csv_import_config_schema_has_required_fields(): void
    {
        $adapter = new CsvProductsImport();
        $schema = $adapter->configSchema();

        $this->assertArrayHasKey('file_path', $schema);
        $this->assertArrayHasKey('delimiter', $schema);
        $this->assertArrayHasKey('default_user_id', $schema);
        $this->assertTrue($schema['file_path']['required']);
    }

    public function test_orders_export_config_schema_has_fields(): void
    {
        $adapter = new OrdersExport();
        $schema = $adapter->configSchema();

        $this->assertArrayHasKey('days_back', $schema);
        $this->assertArrayHasKey('statuses', $schema);
        $this->assertArrayHasKey('delimiter', $schema);
    }

    public function test_csv_import_read_parses_rows(): void
    {
        $csv = tmpfile();
        fwrite($csv, "title,sku,price,stock\n");
        fwrite($csv, "Widget,W-001,1000,5\n");
        fwrite($csv, "Gadget,G-002,2500,10\n");
        $path = stream_get_meta_data($csv)['uri'];

        $adapter = new CsvProductsImport();
        $rows = iterator_to_array($adapter->read(['file_path' => $path, 'delimiter' => ',']));

        $this->assertCount(2, $rows);
        $this->assertSame('Widget', $rows[0]['title']);
        $this->assertSame('W-001', $rows[0]['sku']);
        $this->assertSame('Gadget', $rows[1]['title']);
    }

    public function test_csv_import_read_throws_for_missing_file(): void
    {
        $adapter = new CsvProductsImport();

        $this->expectException(\RuntimeException::class);
        iterator_to_array($adapter->read(['file_path' => '/nonexistent/path.csv']));
    }

    public function test_csv_import_process_creates_product(): void
    {
        $adapter = new CsvProductsImport();

        $result = $adapter->process([
            'title' => 'Test Product',
            'sku' => 'TP-001',
            'price' => '999',
            'stock' => '3',
        ], ['default_user_id' => 1]);

        $this->assertSame('created', $result['action']);
        $this->assertDatabaseHas('ads', ['sku' => 'TP-001', 'title' => 'Test Product']);
    }

    public function test_csv_import_process_updates_existing_by_sku(): void
    {
        $adapter = new CsvProductsImport();

        // Create first
        $adapter->process([
            'title' => 'Original',
            'sku' => 'UPD-001',
            'price' => '500',
            'stock' => '1',
        ], ['default_user_id' => 1]);

        // Update
        $result = $adapter->process([
            'title' => 'Updated Name',
            'sku' => 'UPD-001',
            'price' => '750',
            'stock' => '5',
        ], ['default_user_id' => 1]);

        $this->assertSame('updated', $result['action']);
        $this->assertDatabaseHas('ads', ['sku' => 'UPD-001', 'title' => 'Updated Name', 'price' => 750]);
    }

    public function test_csv_import_process_error_for_missing_title(): void
    {
        $adapter = new CsvProductsImport();

        $result = $adapter->process(['sku' => 'NOTITLE'], []);

        $this->assertSame('error', $result['action']);
    }
}
