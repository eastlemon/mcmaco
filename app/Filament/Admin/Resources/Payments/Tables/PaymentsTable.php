<?php

namespace App\Filament\Admin\Resources\Payments\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->url(fn ($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null),

                TextColumn::make('provider')
                    ->label('Провайдер')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->colors([
                        'warning' => \App\Models\Payment::STATUS_PENDING,
                        'success' => \App\Models\Payment::STATUS_SUCCEEDED,
                        'danger' => [\App\Models\Payment::STATUS_CANCELED, \App\Models\Payment::STATUS_FAILED],
                        'gray' => \App\Models\Payment::STATUS_REFUNDED,
                    ])
                    ->formatStateUsing(fn ($state) => \App\Models\Payment::STATUSES[$state] ?? $state),

                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('provider_payment_id')
                    ->label('ID платежа')
                    ->copyable()
                    ->limit(20),

                TextColumn::make('paid_at')
                    ->label('Оплачен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(\App\Models\Payment::STATUSES),

                SelectFilter::make('provider')
                    ->label('Провайдер')
                    ->options(['yookassa' => 'ЮKassa']),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
