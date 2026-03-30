<?php

namespace App\Filament\Admin\Resources\Ads\Tables;

use App\Models\Ad;
use App\Notifications\AdStatusChanged;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('RUB', locale: 'ru')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('condition')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'closed' => 'Closed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Ad $record) => $record->status === 'pending' || $record->status === 'rejected')
                    ->action(function (Ad $record) {
                        $record->update(['status' => 'active']);
                        $record->user?->notify(new AdStatusChanged($record, 'active'));
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Ad $record) => $record->status === 'pending' || $record->status === 'active')
                    ->action(function (Ad $record) {
                        $record->update(['status' => 'rejected']);
                        $record->user?->notify(new AdStatusChanged($record, 'rejected'));
                    }),
                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Ad $record) => $record->status === 'active')
                    ->action(function (Ad $record) {
                        $record->update(['status' => 'closed']);
                        $record->user?->notify(new AdStatusChanged($record, 'closed'));
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
