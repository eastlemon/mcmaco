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

    protected static ?string $title = null; // use getTitle()

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.pipelines.logs.status'))
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
                    ->label(__('filament.pipelines.logs.message'))
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('processed')
                    ->label(__('filament.pipelines.logs.processed'))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('created')
                    ->label(__('filament.pipelines.logs.created'))
                    ->alignRight()
                    ->color('success'),

                Tables\Columns\TextColumn::make('updated')
                    ->label(__('filament.pipelines.logs.updated'))
                    ->alignRight()
                    ->color('info'),

                Tables\Columns\TextColumn::make('errors')
                    ->label(__('filament.pipelines.logs.errors'))
                    ->alignRight()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('photos')
                    ->label(__('filament.pipelines.logs.photos'))
                    ->alignRight()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.pipelines.logs.date'))
                    ->dateTime('d.m.Y H:i:s'),
            ])
            ->poll('10s')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament.pipelines.logs.status'))
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