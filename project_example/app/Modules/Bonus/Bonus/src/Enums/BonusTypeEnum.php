<?php

namespace App\Modules\Bonus\Bonus\src\Enums;

enum BonusTypeEnum: string
{
    case Accrual = 'accrual';
    case Withdrawal = 'withdrawal';

    public function toString() : string {
        return match($this) {
            self::Accrual => 'Начисление',
            self::Withdrawal => 'Списание',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Accrual->value => 'Начисление',
            self::Withdrawal->value => 'Списание',
        ];
    }
}
