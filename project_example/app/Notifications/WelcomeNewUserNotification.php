<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use User\Models\User;

class WelcomeNewUserNotification extends Notification
{
    use Queueable;

    protected User $user;
    protected string $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Добро пожаловать на сайт!')
            ->greeting('Здравствуйте, ' . $this->user->name . '!')
            ->line('Ваш аккаунт был создан автоматически при оформлении заказа.')
            ->line('Данные для входа:')
            ->line('Email: ' . $this->user->email)
            ->line('Пароль: ' . $this->password)
            ->line('Мы рекомендуем сменить пароль в настройках профиля после входа.')
            ->action('Подтвердить email', $this->verificationUrl($notifiable))
            ->line('Спасибо, что выбрали нас!');
    }
}
