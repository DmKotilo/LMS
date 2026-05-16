<?php

namespace App\Support;

use User\Models\User;

class UserBlockHelper
{
    public static function isBlocked(?User $user): bool
    {
        return (bool) ($user && $user->is_blocked);
    }

    public static function supportEmail(): string
    {
        return 'zakaz@primebeefmoscow.ru';
    }

    public static function blockMessage(): string
    {
        return sprintf(
            'Ваш аккаунт заблокирован. Свяжитесь с %s для разблокировки.',
            self::supportEmail()
        );
    }
}

