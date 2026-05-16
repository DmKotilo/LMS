<?php

namespace App\Notifications;

use Bonus\Models\UserBonusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewBonusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected int $amount
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Спасибо за ваш отзыв! Вам начислены бонусы")
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Благодарим вас за оставленный отзыв!")
            ->line("В качестве бонуса за ваш отзыв вам начислено {$this->amount} бонусов.")
            ->line('Эти бонусы можно использовать для оплаты заказов в нашем магазине.')
            ->action('Перейти в профиль', url('/profile'))
            ->line('Спасибо, что с нами!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'review_bonus',
            'amount' => $this->amount,
            'message' => "Вам начислено {$this->amount} бонусов за оставленный отзыв",
        ];
    }
}
