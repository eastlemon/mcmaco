<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use App\Filament\Admin\Resources\Pipelines\Schemas\PipelineForm;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Same padding as EditPipeline: every dynamic config field must have
        // state to entangle to, even for records saved before a key existed.
        $data['config'] = array_replace(
            PipelineForm::defaultConfigFor($data['type'] ?? null, $data['adapter'] ?? null),
            $data['config'] ?? [],
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run')
                ->label(__('filament.pipelines.actions.run_now'))
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function (Pipeline $record) {
                    RunPipelineJob::dispatch($record);
                    Notification::make()
                        ->success()
                        ->title(__('filament.pipelines.notifications.started'))
                        ->body("{$record->name} поставлен в очередь выполнения")
                        ->send();
                }),
        ];
    }
}