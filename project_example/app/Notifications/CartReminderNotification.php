<?php

namespace App\Notifications;

use Cart\Models\UserCart;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CartReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected UserCart $cart
    ) {
        // ...
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $items = $this->cart->items->map(function ($item) {
            $name = $item->item?->name ?? 'Неизвестный товар';
            return "- {$name} ({$item->quantity} шт.)";
        })->join("\n");

        return (new MailMessage)
            ->subject('Напоминание о корзине')
            ->line('Вы не завершили покупку. У вас в корзине:')
            ->line($items)
            ->action('Вернуться в корзину', route('cart.index'))
            ->line('Спасибо, что пользуетесь нашим сервисом!');
    }
}
