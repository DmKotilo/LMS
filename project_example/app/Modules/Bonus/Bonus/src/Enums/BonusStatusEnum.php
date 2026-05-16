<?php

namespace App\Modules\Bonus\Bonus\src\Enums;

enum BonusStatusEnum: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Spent = 'spent';

    public function toString(): string
    {
        return match($this) {
            self::Active  => 'Начислены',
            self::Expired => 'Сгорели',
            self::Spent   => 'Потрачены',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Active->value  => 'Начислены',
            self::Expired->value => 'Сгорели',
            self::Spent->value   => 'Потрачены',
        ];
    }
}
