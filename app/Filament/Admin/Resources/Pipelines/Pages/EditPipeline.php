<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineFormSchema;
use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use Filament\Resources\Pages\EditRecord;

class EditPipeline extends EditRecord
{
    use PipelineFormSchema;

    protected static string $resource = PipelineResource::class;

    protected function getFormSchema(): array
    {
        return $this->pipelineFormSchema();
    }
}
