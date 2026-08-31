<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('filament.orders.fields.number'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('customer_name')
                    ->label(__('filament.orders.fields.client'))
                    ->searchable(),

                TextColumn::make('customer_phone')
                    ->label(__('filament.orders.fields.phone'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('filament.orders.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'confirmed', 'processing' => 'info',
                        'paid' => 'success',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'done' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state),

                TextColumn::make('total')
                    ->label(__('filament.orders.fields.amount'))
                    ->money('RUB', locale: 'ru')
                    ->sortable(),

                TextColumn::make('deliveryMethod.name')
                    ->label(__('filament.orders.fields.delivery'))
                    ->placeholder(fn (Order $record) => Order::DELIVERY_METHODS[$record->delivery_method] ?? $record->delivery_method),

                TextColumn::make('tracking_number')
                    ->label(__('filament.orders.fields.tracking_short'))
                    ->searchable()
                    ->copyable()
                    ->limit(20)
                    ->placeholder('—'),

                TextColumn::make('is_quick_order')
                    ->label(__('filament.orders.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? '⚡ ' . __('filament.orders.type.quick') : __('filament.orders.type.regular'))
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label(__('filament.orders.fields.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.orders.fields.status'))
                    ->options(Order::STATUSES),

                SelectFilter::make('delivery_method_id')
                    ->label(__('filament.orders.fields.delivery'))
                    ->relationship('deliveryMethod', 'name'),

                SelectFilter::make('is_quick_order')
                    ->label(__('filament.orders.fields.type'))
                    ->options([
                        '1' => __('filament.orders.type.quick'),
                        '0' => __('filament.orders.type.regular'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.common.view')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}