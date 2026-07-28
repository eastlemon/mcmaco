<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdmin extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Новый заказ {$this->order->order_number} — mcmaco")
            ->greeting('Новый заказ!')
            ->line("Заказчик: {$this->order->customer_name}")
            ->line("Телефон: {$this->order->customer_phone}")
            ->line("Тип: " . ($this->order->is_quick_order ? 'Быстрый заказ (1 клик)' : 'Обычный заказ'));

        if ($this->order->customer_email) {
            $mail->line("Email: {$this->order->customer_email}");
        }

        $mail->line('')
            ->line('Состав заказа:');

        foreach ($this->order->items as $item) {
            $mail->line("• {$item->title_snapshot} ×{$item->qty} — {$item->formatted_subtotal}");
        }

        $mail->line('')
            ->line("Доставка: {$this->order->delivery_method_label}")
            ->line("Сумма: {$this->order->formatted_total}")
            ->action('Открыть в админке', url('/admin'));

        return $mail;
    }
}