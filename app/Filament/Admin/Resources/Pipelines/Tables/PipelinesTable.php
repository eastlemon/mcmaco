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
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state) => $state === 'export' ? 'info' : 'success')
                    ->formatStateUsing(fn (string $state) => Pipeline::TYPES[$state] ?? $state),

                TextColumn::make('adapter')
                    ->label('Адаптер')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('format')
                    ->label('Формат')
                    ->formatStateUsing(fn ($state) => is_string($state) ? mb_strtoupper($state) : $state),

                TextColumn::make('lastRun.status')
                    ->label('Последний запуск')
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
                    ->label('Когда')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(Pipeline::TYPES),

                SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([1 => 'Активные', 0 => 'Отключённые']),
            ])
            ->recordActions([
                ViewAction::make()->label('Просмотр'),

                Action::make('run')
                    ->label('Запустить')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Pipeline $record) {
                        \App\Jobs\RunPipelineJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Пайплайн запущен')
                            ->body("{$record->name} поставлен в очередь")
                            ->send();
                    }),
            ]);
    }
}