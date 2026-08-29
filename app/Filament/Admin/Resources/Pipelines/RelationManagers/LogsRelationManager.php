<?php

namespace App\Filament\Admin\Resources\Pipelines\RelationManagers;

use App\Models\PipelineLog;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'История запусков';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => PipelineLog::STATUSES[$state] ?? $state),

                Tables\Columns\TextColumn::make('message')
                    ->label('Сообщение')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('processed')
                    ->label('Обработано')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('created')
                    ->label('Создано')
                    ->alignRight()
                    ->color('success'),

                Tables\Columns\TextColumn::make('updated')
                    ->label('Обновлено')
                    ->alignRight()
                    ->color('info'),

                Tables\Columns\TextColumn::make('errors')
                    ->label('Ошибок')
                    ->alignRight()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i:s'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(PipelineLog::STATUSES),
            ])
            ->actions([
                ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data, PipelineLog $record): array {
                        $data['details_json'] = json_encode($record->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        return $data;
                    }),
            ]);
    }
}