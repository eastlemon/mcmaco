<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = Order::STATUSES[$this->order->status] ?? $this->order->status;
        $oldLabel = Order::STATUSES[$this->oldStatus] ?? $this->oldStatus;

        $mail = (new MailMessage)
            ->subject("Статус заказа {$this->order->order_number} изменён — mcmaco")
            ->greeting('Здравствуйте!')
            ->line("Статус заказа {$this->order->order_number} изменён:")
            ->line("{$oldLabel} → {$statusLabel}");

        // Helpful context per status
        $mail->line(match ($this->order->status) {
            Order::STATUS_CONFIRMED => 'Заказ подтверждён. Ожидает оплаты.',
            Order::STATUS_PAID => 'Заказ оплачен. Начинаем сборку.',
            Order::STATUS_PROCESSING => 'Заказ собирается на складе.',
            Order::STATUS_SHIPPED => 'Заказ отправлен.' . ($this->order->delivery_address ? " Адрес: {$this->order->delivery_address}" : ''),
            Order::STATUS_DELIVERED => 'Заказ доставлен. Спасибо за покупку!',
            Order::STATUS_DONE => 'Заказ завершён. Будем рады видеть вас снова!',
            Order::STATUS_CANCELLED => 'Заказ отменён.' . ($this->order->comment ? " Причина: {$this->order->comment}" : ''),
            default => '',
        });

        $mail->action('Посмотреть заказ', route('orders.show', $this->order));

        return $mail;
    }
}