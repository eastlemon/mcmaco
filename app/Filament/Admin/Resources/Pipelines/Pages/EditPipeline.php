<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use App\Filament\Admin\Resources\Pipelines\Schemas\PipelineForm;
use Filament\Resources\Pages\EditRecord;

class EditPipeline extends EditRecord
{
    protected static string $resource = PipelineResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pad missing adapter config keys with defaults so every dynamic
        // field has state to entangle to (older records may lack newer keys).
        $data['config'] = array_replace(
            PipelineForm::defaultConfigFor($data['type'] ?? null, $data['adapter'] ?? null),
            $data['config'] ?? [],
        );

        return $data;
    }
}