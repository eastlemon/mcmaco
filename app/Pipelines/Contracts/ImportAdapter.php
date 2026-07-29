<?php

namespace App\Pipelines\Contracts;

interface ImportAdapter
{
    /**
     * Read data from the source defined in config and return iterable rows.
     *
     * @param array $config Pipeline config
     * @return \Generator<int, array>
     */
    public function read(array $config): \Generator;

    /**
     * Process a single row — create/update model.
     *
     * @param array $row
     * @param array $config
     * @return array ['action' => 'created'|'updated'|'skipped'|'error', 'message' => ?string]
     */
    public function process(array $row, array $config): array;

    /**
     * Describe config fields for Filament admin forms.
     *
     * @return array
     */
    public function configSchema(): array;
}