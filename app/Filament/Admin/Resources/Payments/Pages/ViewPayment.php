<?php

namespace App\Filament\Admin\Resources\Payments\Pages;

use App\Filament\Admin\Resources\Payments\PaymentResource;
use App\Models\Payment;
use App\Services\YooKassaService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * @property Payment $record
 */
class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('check_status')
                ->label('Проверить статус')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $service = app(YooKassaService::class);
                    try {
                        $service->checkPaymentStatus($this->record);
                        Notification::make()
                            ->title('Статус обновлён')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Ошибка: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->status === Payment::STATUS_PENDING),

            Actions\Action::make('refund')
                ->label('Возврат')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(YooKassaService::class);
                    try {
                        $service->refund($this->record);
                        Notification::make()
                            ->title('Возврат оформлен')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Ошибка возврата: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->status === Payment::STATUS_SUCCEEDED),
        ];
    }
}
