<?php

namespace User\Observers;

use App\Services\Bitrix24\BitrixSubscriptionService;
use Bonus\Models\BonusCard;
use City\Models\City;
use Loyalty\Models\LoyaltyLevel;
use User\Models\Subscription;
use User\Models\User;
use UserSetting\Models\UserSetting;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        $subscription = Subscription::where('email', $user->email)
            ->whereNull('user_id')
            ->first();

        if ($subscription) {
            $subscription->update([
                'user_id' => $user->id,
            ]);

            // включаем email-уведомления у пользователя
            $userSettings = UserSetting::firstOrCreate(
                ['user_id' => $user->id],
                []
            );

            $userSettings->update([
                'email_notifications' => true,
            ]);
        }
        $level = LoyaltyLevel::where('name', 'Rare')->first();

        if ($level) {
            $user->loyaltyLevel()->create([
                'loyalty_level_id' => $level->id,
            ]);
        }
        UserSetting::create([
            'user_id' => $user->id,
            'comment_notifications' => false,
            'sale_notifications' => false,
            'email_notifications' => false,
            'sms_notifications' => false,
            'messenger_notifications' => false,
            'often_type' => null,
            'favorite_categories' => false,
        ]);

        do {
            $number = str_pad(random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (BonusCard::where('number', $number)->exists());

        BonusCard::create([
            'user_id' => $user->id,
            'is_active' => true,
            'number' => $number,
        ]);

        $moscow = City::where('name', 'Москва')->first();
        if ($moscow) {
            session()->put('user_city', [
                'city_id' => $moscow->id,
                'city' => $moscow->name,
                'latitude' => $moscow->latitude,
                'longitude' => $moscow->longitude,
            ]);
        }


        app(BitrixSubscriptionService::class)->syncUser($user);
    }


    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
