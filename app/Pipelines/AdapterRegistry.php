<?php

namespace App\Pipelines;

use App\Models\Pipeline;
use App\Pipelines\Contracts\ExportAdapter;
use App\Pipelines\Contracts\ImportAdapter;
use App\Pipelines\Adapters\CsvProductsImport;
use App\Pipelines\Adapters\OrdersExport;
use InvalidArgumentException;

class AdapterRegistry
{
    private array $importAdapters = [];
    private array $exportAdapters = [];

    public function __construct()
    {
        $this->registerImport('csv_products', CsvProductsImport::class);
        $this->registerExport('orders_export', OrdersExport::class);
    }

    public function registerImport(string $code, string $class): void
    {
        $this->importAdapters[$code] = $class;
    }

    public function registerExport(string $code, string $class): void
    {
        $this->exportAdapters[$code] = $class;
    }

    public function getImportAdapter(string $code): ImportAdapter
    {
        if (!isset($this->importAdapters[$code])) {
            throw new InvalidArgumentException("Unknown import adapter: {$code}");
        }
        return app($this->importAdapters[$code]);
    }

    public function getExportAdapter(string $code): ExportAdapter
    {
        if (!isset($this->exportAdapters[$code])) {
            throw new InvalidArgumentException("Unknown export adapter: {$code}");
        }
        return app($this->exportAdapters[$code]);
    }

    public function getAdapter(string $code, string $type): ImportAdapter|ExportAdapter
    {
        return $type === Pipeline::TYPE_EXPORT
            ? $this->getExportAdapter($code)
            : $this->getImportAdapter($code);
    }

    public function listImports(): array
    {
        return $this->importAdapters;
    }

    public function listExports(): array
    {
        return $this->exportAdapters;
    }

    public function listAll(): array
    {
        return [
            'import' => $this->importAdapters,
            'export' => $this->exportAdapters,
        ];
    }
}