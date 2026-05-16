<?php

namespace Bonus\Observers;

use Alert\Models\Alert;
use Alert\Enums\AlertTypeEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\UserBonusHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;


class BonusObserver
{
    public function created(UserBonusHistory $bonusHistory): void
    {
        $this->sendNotification($bonusHistory, 'created');
    }

    public function updated(UserBonusHistory $bonusHistory): void
    {
        // Отправляем уведомление только если is_manual был изменен с false/null на true
        if ($bonusHistory->is_manual && $bonusHistory->wasChanged('is_manual')) {
            $this->sendNotification($bonusHistory, 'updated');
        }
    }

    protected function sendNotification(UserBonusHistory $bonusHistory, string $event): void
    {


        if (! $bonusHistory->is_manual) {
            return;
        }

        $user = $bonusHistory->user;
        if (! $user) {
            return;
        }

        if (! $user->email) {
            return;
        }

        try {
            $user->notify(new \App\Notifications\ManualBonusOperationNotification($bonusHistory));

            // Определяем ключи локализации в зависимости от типа операции
            $isWithdrawal = $bonusHistory->type === BonusTypeEnum::Withdrawal;

            $titleKey = $isWithdrawal
                ? 'alerts.manualBonusWithdrawalTitle'
                : 'alerts.manualBonusTitle';

            $messageKey = $isWithdrawal
                ? 'alerts.manualBonusWithdrawalMessage'
                : 'alerts.manualBonusMessage';

            $countKey = $isWithdrawal
                ? 'alerts.manualBonusWithdrawalCount'
                : 'alerts.manualBonusCount';

            // Создаем алерт о ручном начислении/списании бонусов
            // Для списания дата истечения не нужна
            $expiresAt = null;
            if (!$isWithdrawal && $bonusHistory->expires_at) {
                $expiresAt = Carbon::parse($bonusHistory->expires_at)->format('d.m.Y');
            } elseif (!$isWithdrawal) {
                $expiresAt = Carbon::now()->addDays(90)->format('d.m.Y');
            }

            Alert::create([
                'user_id' => $user->id,
                'type' => AlertTypeEnum::ManualBonus,
                'title' => $titleKey,
                'message' => $messageKey,
                'button_text' => 'alerts.buttonDetails',
                'button_link' => route('user.profile.bonus.history'),
                'bonus_count' => (int) abs($bonusHistory->amount),
                'bonus_message' => $countKey,
                'bonus_date' => $expiresAt,
                'is_read' => false,
            ]);


        } catch (\Exception $e) {

        }
    }
}
