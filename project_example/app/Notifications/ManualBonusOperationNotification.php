<?php

namespace App\Notifications;

use Bonus\Models\UserBonusHistory;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue; // <-- Закомментировать
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// class ManualBonusOperationNotification extends Notification implements ShouldQueue // <-- Убрать ShouldQueue
class ManualBonusOperationNotification extends Notification // <-- Оставить только это
{
    use Queueable;

    public function __construct(
        protected UserBonusHistory $bonusHistory
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $type = $this->bonusHistory->type->toString();
        $amount = abs($this->bonusHistory->amount);
        $reason = $this->bonusHistory->reason?->toString() ?? 'Без указания причины';
        $comment = $this->bonusHistory->comment;

        return (new MailMessage)
            ->subject("{$type} бонусов")
            ->greeting('Здравствуйте!')
            ->line("{$type} на сумму {$amount} бонусов.")
            ->line("Причина: {$reason}")
            ->when($comment, fn ($mail) => $mail->line("Комментарий: {$comment}"))
            ->when(
                $this->bonusHistory->type->value === 'accrual' && $this->bonusHistory->active_date,
                fn ($mail) => $mail->line('Бонусы действительны до: ' . $this->bonusHistory->expires_at->format('d.m.Y'))
            )
            ->line('Спасибо за участие в программе лояльности!');
    }

    public function toArray($notifiable): array
    {
        return [
            'bonus_history_id' => $this->bonusHistory->id,
            'type' => $this->bonusHistory->type->value,
            'amount' => $this->bonusHistory->amount,
            'reason' => $this->bonusHistory->reason?->toString(),
            'comment' => $this->bonusHistory->comment,
            'active_date' => $this->bonusHistory->active_date,
        ];
    }
}
