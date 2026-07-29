<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use Filament\Resources\Pages\ListRecords;

class ListPipelines extends ListRecords
{
    protected static string $resource = PipelineResource::class;
}