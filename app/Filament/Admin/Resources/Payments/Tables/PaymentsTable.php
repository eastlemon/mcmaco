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
                    ->label(__('filament.payments.order'))
                    ->searchable()
                    ->url(fn ($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null),

                TextColumn::make('provider')
                    ->label(__('filament.payments.provider'))
                    ->badge(),

                TextColumn::make('status')
                    ->label(__('filament.payments.status'))
                    ->badge()
                    ->colors([
                        'warning' => \App\Models\Payment::STATUS_PENDING,
                        'success' => \App\Models\Payment::STATUS_SUCCEEDED,
                        'danger' => [\App\Models\Payment::STATUS_CANCELED, \App\Models\Payment::STATUS_FAILED],
                        'gray' => \App\Models\Payment::STATUS_REFUNDED,
                    ])
                    ->formatStateUsing(fn ($state) => \App\Models\Payment::STATUSES[$state] ?? $state),

                TextColumn::make('amount')
                    ->label(__('filament.payments.amount'))
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('provider_payment_id')
                    ->label(__('filament.payments.payment_id'))
                    ->copyable()
                    ->limit(20),

                TextColumn::make('paid_at')
                    ->label(__('filament.payments.paid_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament.payments.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.payments.status'))
                    ->options(\App\Models\Payment::STATUSES),

                SelectFilter::make('provider')
                    ->label(__('filament.payments.provider'))
                    ->options(['yookassa' => 'ЮKassa']),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
