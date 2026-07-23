<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Подтвердить')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Order $record) => $record->status === Order::STATUS_NEW)
                ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_CONFIRMED])),

            Action::make('ship')
                ->label('Отправить')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn (Order $record) => in_array($record->status, [Order::STATUS_CONFIRMED, Order::STATUS_PAID, Order::STATUS_PROCESSING]))
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