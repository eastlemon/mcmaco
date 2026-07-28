<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification
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
            ->subject("Заказ {$this->order->order_number} оформлен — mcmaco")
            ->greeting('Здравствуйте!')
            ->line("Ваш заказ {$this->order->order_number} успешно оформлен.")
            ->line('Состав заказа:');

        foreach ($this->order->items as $item) {
            $mail->line("• {$item->title_snapshot} ×{$item->qty} — {$item->formatted_subtotal}");
        }

        $mail->line('')
            ->line("Доставка: {$this->order->delivery_method_label}")
            ->line("Итого: {$this->order->formatted_total}")
            ->action('Посмотреть заказ', route('orders.show', $this->order))
            ->line('Мы свяжемся с вами для подтверждения. Спасибо за заказ!');

        return $mail;
    }
}