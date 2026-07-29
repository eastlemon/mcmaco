<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use App\Jobs\RunPipelineJob;
use App\Models\Pipeline;
use App\Pipelines\AdapterRegistry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Tables;

class ViewPipeline extends ViewRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run')
                ->label('Запустить сейчас')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function (Pipeline $record) {
                    RunPipelineJob::dispatch($record);
                    Notification::make()
                        ->success()
                        ->title('Пайплайн запущен')
                        ->body("{$record->name} поставлен в очередь выполнения")
                        ->send();
                }),
        ];
    }
}