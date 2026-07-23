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
                    ->label('№ заказа')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable(),

                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Статус')
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
                    ->label('Сумма')
                    ->money('RUB', locale: 'ru')
                    ->sortable(),

                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->formatStateUsing(fn (string $state) => Order::DELIVERY_METHODS[$state] ?? $state),

                TextColumn::make('is_quick_order')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? '⚡ 1-клик' : 'Обычный')
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::STATUSES),

                SelectFilter::make('delivery_method')
                    ->label('Доставка')
                    ->options(Order::DELIVERY_METHODS),

                SelectFilter::make('is_quick_order')
                    ->label('Тип заказа')
                    ->options([
                        '1' => 'Быстрый заказ',
                        '0' => 'Обычный',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}