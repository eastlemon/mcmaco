<?php

namespace App\Filament\Admin\Resources\Ads\Tables;

use App\Models\Ad;
use App\Notifications\AdStatusChanged;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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
                ImageColumn::make('cover')
                    ->label(__('filament.ads.fields.photo'))
                    ->getStateUsing(fn (Ad $record): ?string => $record->cover_image?->url)
                    ->circular(),
                TextColumn::make('title')
                    ->label(__('filament.ads.fields.title'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('price')
                    ->money('RUB', locale: 'ru')
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('category.name')
                    ->label(__('filament.ads.fields.category'))
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('condition')
                    ->badge(),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->label(__('filament.ads.fields.featured_short')),
                TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'pending' => __('filament.ads.status.pending'),
                        'active' => __('filament.ads.status.active'),
                        'closed' => __('filament.ads.status.closed'),
                        'rejected' => __('filament.ads.status.rejected'),
                    ]),
                SelectFilter::make('is_featured')
                    ->options([
                        '1' => __('filament.ads.featured.yes'),
                        '0' => __('filament.ads.featured.no'),
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
