<?php

namespace App\Pipelines\Contracts;

interface ExportAdapter
{
    /**
     * Query data to export.
     *
     * @param array $config
     * @return \Illuminate\Support\Collection
     */
    public function query(array $config): \Illuminate\Support\Collection;

    /**
     * Format a single row for export.
     *
     * @param mixed $item
     * @param array $config
     * @return array
     */
    public function format(mixed $item, array $config): array;

    /**
     * Write the full export to file.
     *
     * @param \Illuminate\Support\Collection $items
     * @param array $config
     * @return string Output file path
     */
    public function write(\Illuminate\Support\Collection $items, array $config): string;

    /**
     * Describe config fields for Filament admin forms.
     *
     * @return array
     */
    public function configSchema(): array;
}