<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyNewEmail extends Notification
{
    protected $newEmail;

    public function __construct(string $newEmail)
    {
        $this->newEmail = $newEmail;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }
    protected function verificationUrl($notifiable)
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
            ->subject('Подтвердите новый email')
            ->line('Пожалуйста, подтвердите новый адрес электронной почты.')
            ->action('Подтвердить', $this->verificationUrl($notifiable));
    }
}
