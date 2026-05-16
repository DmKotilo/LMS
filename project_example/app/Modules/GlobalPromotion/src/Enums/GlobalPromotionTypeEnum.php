<?php

namespace App\Modules\GlobalPromotion\src\Enums;

enum GlobalPromotionTypeEnum: string
{
    case Cashback = 'cashback';
    case ProductCashback = 'product_cashback';
    case Bonus = 'bonus';


    public function toString() : string {
        return match($this) {
            self::Cashback => 'Кэшбек',
            self::ProductCashback => 'Кэшбек на определенные товары',
            self::Bonus => 'Баллы',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Cashback->value => 'Кэшбек',
            self::ProductCashback->value => 'Кэшбек на определенные товары',
            self::Bonus->value => 'Баллы',
        ];
    }
}
