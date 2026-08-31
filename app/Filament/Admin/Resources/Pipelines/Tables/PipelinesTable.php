<?php

namespace App\Filament\Admin\Resources\Pipelines\Tables;

use App\Models\Pipeline;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PipelinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.pipelines.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('filament.pipelines.fields.type'))
                    ->badge()
                    ->color(fn (string $state) => $state === 'export' ? 'info' : 'success')
                    ->formatStateUsing(fn (string $state) => Pipeline::TYPES[$state] ?? $state),

                TextColumn::make('adapter')
                    ->label(__('filament.pipelines.fields.adapter'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('format')
                    ->label(__('filament.pipelines.fields.format'))
                    ->formatStateUsing(fn ($state) => is_string($state) ? mb_strtoupper($state) : $state),

                TextColumn::make('lastRun.status')
                    ->label(__('filament.pipelines.fields.last_run'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                TextColumn::make('lastRun.created_at')
                    ->label(__('filament.pipelines.fields.when'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label(__('filament.pipelines.fields.active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('filament.pipelines.fields.type'))
                    ->options(Pipeline::TYPES),

                SelectFilter::make('is_active')
                    ->label(__('filament.pipelines.fields.status'))
                    ->options([1 => __('filament.pipelines.status.active'), 0 => __('filament.pipelines.status.disabled')]),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.common.view')),

                Action::make('run')
                    ->label(__('filament.pipelines.actions.run'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Pipeline $record) {
                        \App\Jobs\RunPipelineJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title(__('filament.pipelines.notifications.started'))
                            ->body("{$record->name} поставлен в очередь")
                            ->send();
                    }),
            ]);
    }
}