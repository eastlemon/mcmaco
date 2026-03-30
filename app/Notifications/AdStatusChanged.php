<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Ad $ad,
        public string $status,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = match ($this->status) {
            'active' => 'одобрено',
            'rejected' => 'отклонено',
            'closed' => 'закрыто',
            default => $this->status,
        };

        $message = (new MailMessage)
            ->subject('Статус объявления изменён')
            ->greeting('Привет!')
            ->line("Ваше объявление «{$this->ad->title}» было {$statusLabel}.")
            ->action('Посмотреть объявление', route('ads.show', $this->ad));

        if ($this->reason) {
            $message->line('Причина: ' . $this->reason);
        }

        return $message;
    }
}
