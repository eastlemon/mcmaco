<?php

namespace App\Notifications;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChatMessage extends Notification
{
    use Queueable;

    public function __construct(
        public Chat $chat,
        public Message $message,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $adTitle = $this->chat->ad->title ?? 'Объявление';

        return (new MailMessage)
            ->subject('Новое сообщение по объявлению')
            ->greeting('Привет!')
            ->line("Новое сообщение по объявлению: {$adTitle}.")
            ->line('От: ' . ($this->message->user->name ?? 'Пользователь'))
            ->line($this->message->message)
            ->action('Перейти в чат', route('chats.show', $this->chat))
            ->line('Если вы не ожидали это письмо — просто проигнорируйте его.');
    }
}
