<?php

namespace App\Notifications;

use App\Modules\Order\src\Enums\OrderStatusEnum;
use Illuminate\Bus\Queueable;use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Order\Models\Order;
use User\Models\User;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected OrderStatusEnum $oldStatus,
        protected OrderStatusEnum $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Изменение статуса заказа №'.$this->order->id)
            ->greeting('Здравствуйте, '.$notifiable->name)
            ->line('Статус вашего заказа №'.$this->order->id .' '. 'изменился')
            ->line('Было: '.$this->oldStatus->toString())
            ->line('Стало: '.$this->newStatus->toString())
            ->action('Посмотреть заказ', route('user.profile.orders.history.show', ['order' => $this->order->id]))
            ->line('Спасибо, что выбрали нас!');
    }
}
