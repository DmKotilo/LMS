<?php

namespace App\Notifications;

use ContactForm\Models\CallContactForm;
use Illuminate\Bus\Queueable;use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use User\Models\User;

class NewCallContactFormNotification extends Notification
{
    use Queueable;

    protected CallContactForm $form;

    public function __construct(CallContactForm $form)
    {
        $this->form = $form;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Новая заявка!')
            ->line('Тип заявки: ' . $this->form->type->toString())
            ->line('Имя: ' . $this->form->name)
            ->line('Телефон: ' . $this->form->phone)
            ->line('Комментарий: ' . ($this->form->comment ?? '-'))
            ->line('Время отправки формы: ' . $this->form->created_at->format('d.m.Y H:i'))
            ->line('Время для звонка: ' . ($this->form->time_interval ?? '-'));
    }
}
