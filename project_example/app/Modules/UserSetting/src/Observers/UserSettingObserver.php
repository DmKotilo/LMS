<?php

namespace UserSetting\Observers;

use App\Services\Bitrix24\BitrixSubscriptionService;
use UserSetting\Models\UserSetting;

class UserSettingObserver
{
    public function saved(UserSetting $userSetting): void
    {
        if (
            !$userSetting->wasRecentlyCreated
            && !$userSetting->wasChanged([
                'sms_notifications',
                'email_notifications',
                'messenger_notifications',
                'often_type',
            ])
        ) {
            return;
        }

        $user = $userSetting->user;

        if (!$user) {
            return;
        }

        app(BitrixSubscriptionService::class)->syncUser($user);
    }
}

