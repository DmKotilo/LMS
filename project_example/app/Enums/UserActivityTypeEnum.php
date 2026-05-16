<?php

namespace App\Enums;

enum UserActivityTypeEnum: string
{
    case Registration = 'registration';
    case Login = 'login';
    case SocialAuth = 'social_auth';
    case PasswordResetRequest = 'password_reset_request';
    case PasswordReset = 'password_reset';
    case NotificationSent = 'notification_sent';

    public function toString(): string
    {
        return match ($this) {
            self::Registration => 'Регистрация',
            self::Login => 'Вход',
            self::SocialAuth => 'OAuth вход',
            self::PasswordResetRequest => 'Запрос восстановления пароля',
            self::PasswordReset => 'Изменение пароля',
            self::NotificationSent => 'Отправка уведомления',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Registration->value => 'Регистрация',
            self::Login->value => 'Вход',
            self::SocialAuth->value => 'OAuth вход',
            self::PasswordResetRequest->value => 'Запрос восстановления пароля',
            self::PasswordReset->value => 'Изменение пароля',
            self::NotificationSent->value => 'Отправка уведомления',
        ];
    }
}

