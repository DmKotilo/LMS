<?php

namespace App\Modules\Cart\src\Enums;

enum CartStatusEnum: string
{
    case Active = 'active';
    case Closed = 'closed';

    public function toString() : string {
        return match($this) {
            self::Active => 'Активна',
            self::Closed => 'Закрыта',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Active->value => 'Активна',
            self::Closed->value => 'Закрыта',
        ];
    }
}
