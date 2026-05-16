<?php

namespace User\Observers;

use App\Notifications\BirthdayBonusNotification;
use App\Notifications\SubscriptionBonusNotification;
use \User\Models\Subscription;
use Bonus\Services\BonusService;
use Setting\Models\Setting;
use Illuminate\Support\Facades\Auth;

class SubscriptionObserver
{
    protected BonusService $bonusService;

    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    public function created(Subscription $subscription): void
    {
        if ($subscription->is_active) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && $subscription->user_id === $user->id) {
                    $this->awardBonus($user);
                }
            }
        }
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->is_active &&
            $subscription->isDirty('is_active') &&
            $subscription->getOriginal('is_active') === false &&
            Auth::check()) {

            $user = Auth::user();
            if ($user && $subscription->user_id === $user->id) {
                $this->awardBonus($user);
            }
        }
    }

    protected function awardBonus($user): void
    {
        $subscription = Subscription::where('user_id', $user->id)->first();

        if ($subscription && $subscription->is_bonus_expired === false) {
            $bonusAmount = Setting::where('key', 'subscribe_mails_bonus')->first();
            $bonusValue = $bonusAmount ? $bonusAmount->value : 0;

            if ($bonusValue > 0) {
                $this->bonusService->accrueBonus(
                    user: $user,
                    amount: $bonusValue,
                    comment: 'Бонус за подписку на рассылку'
                );

                $subscription->update(['is_bonus_expired' => true]);


                // Отправка email
                if ($user->email && $user->email_verified_at) {
                    $user->notify(new SubscriptionBonusNotification($bonusValue));
                }

            }
        }
    }

    protected function hasReceivedBonus($user): bool
    {
        return $user->bonusHistories()
            ->where('comment', 'Бонус за подписку на рассылку')
            ->exists();
    }
}
