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
                ->label(__('filament.payments.actions.check_status'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $service = app(YooKassaService::class);
                    try {
                        $service->checkPaymentStatus($this->record);
                        Notification::make()
                            ->title(__('filament.payments.notifications.status_updated'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title(__('filament.common.error') . ': ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->status === Payment::STATUS_PENDING),

            Actions\Action::make('refund')
                ->label(__('filament.payments.actions.refund'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(YooKassaService::class);
                    try {
                        $service->refund($this->record);
                        Notification::make()
                            ->title(__('filament.payments.notifications.refunded'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title(__('filament.common.error') . ' ' . __('filament.payments.actions.refund') . ': ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->status === Payment::STATUS_SUCCEEDED),
        ];
    }
}
