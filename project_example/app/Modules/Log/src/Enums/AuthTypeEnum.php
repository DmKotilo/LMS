<?php

namespace Log\Enums;


enum AuthTypeEnum: string
{
    case Login = 'login';
    case Register = 'register';

    public function toString() : string {
        return match($this) {
            self::Login => 'Вход',
            self::Register => 'Регистрация',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Login->value => 'Вход',
            self::Register->value => 'Регистрация',
        ];
    }
}
