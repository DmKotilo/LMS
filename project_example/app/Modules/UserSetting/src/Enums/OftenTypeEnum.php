<?php

namespace App\Modules\UserSetting\src\Enums;

enum OftenTypeEnum: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case SalesOnly = 'sales_only';

    public function toString(): string
    {
        return match($this) {
            self::Daily => 'Ежедневно',
            self::Weekly => 'Еженедельно',
            self::SalesOnly => 'Только при наличии акций',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Daily->value => 'Ежедневно',
            self::Weekly->value => 'Еженедельно',
            self::SalesOnly->value => 'Только при наличии акций',
        ];
    }
}
