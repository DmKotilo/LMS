<?php

namespace App\Notifications;

use Bonus\Models\UserBonusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BirthdayBonusNotification extends Notification
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
            ->subject("🎉 В преддверии дня рождения — дарим вам бонусы!")
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line('В преддверии вашего дня рождения мы дарим вам бонусы.')
            ->line("Вам начислено {$this->amount} бонусов — ими можно оплатить заказы в нашем магазине.")
            ->action('Перейти в профиль', url('/profile'))
            ->line('Желаем отличного праздника! 🎂');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'birthday_bonus',
            'amount' => $this->amount,
            'message' => "В преддверии дня рождения вам начислено {$this->amount} бонусов",
        ];
    }
}
