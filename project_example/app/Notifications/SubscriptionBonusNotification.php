<?php

namespace App\Notifications;

use Bonus\Models\UserBonusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionBonusNotification extends Notification
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
            ->subject("Спасибо за подписку! Вам будут начислены бонусы")
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Благодарим вас за подписку на нашу рассылку!")
            ->line("В качестве бонуса за подписку вам будет начислено {$this->amount} бонусов в течение 24 часов.")
            ->line('Эти бонусы можно использовать для оплаты заказов в нашем магазине.')
            ->action('Перейти в профиль', url('/profile'))
            ->line('Спасибо, что с нами!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'subscription_bonus',
            'amount' => $this->amount,
            'message' => "Вам будет начислено {$this->amount} бонусов за подписку на рассылку в течение 24 часов",
        ];
    }
}
