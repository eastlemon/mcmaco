<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('set_tracking')
                ->label('Трек-номер')
                ->icon('heroicon-o-map-pin')
                ->color('info')
                ->visible(fn (Order $record) => $record->deliveryMethod && $record->deliveryMethod->type !== 'pickup'
                    && in_array($record->status, [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED]))
                ->schema([
                    TextInput::make('tracking_number')
                        ->label('Трек-номер')
                        ->required()
                        ->default(fn (Order $record) => $record->tracking_number),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->update([
                        'tracking_number' => $data['tracking_number'],
                        'status' => Order::STATUS_SHIPPED,
                    ]);
                })
                ->modalButton('Сохранить и отправить'),

            Action::make('confirm')
                ->label('Подтвердить')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Order $record) => $record->status === Order::STATUS_NEW)
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_CONFIRMED])),

            Action::make('process')
                ->label('Собрать')
                ->icon('heroicon-o-cube')
                ->color('info')
                ->visible(fn (Order $record) => in_array($record->status, [Order::STATUS_CONFIRMED, Order::STATUS_PAID]))
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_PROCESSING])),

            Action::make('ship')
                ->label('Отправить')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn (Order $record) => $record->status === Order::STATUS_PROCESSING)
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_SHIPPED])),

            Action::make('complete')
                ->label('Завершить')
                ->icon('heroicon-o-check-badge')
                ->color('gray')
                ->visible(fn (Order $record) => $record->status === Order::STATUS_SHIPPED || $record->status === Order::STATUS_DELIVERED)
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_DONE])),

            Action::make('cancel')
                ->label('Отменить')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (Order $record) => !in_array($record->status, [Order::STATUS_DONE, Order::STATUS_CANCELLED]))
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_CANCELLED]))
                ->requiresConfirmation(),
        ];
    }
}