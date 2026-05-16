<?php

namespace App\Modules\Promotion\src\Enums;

enum PromotionTypeEnum: string
{
    case Discount = 'discount';
    case Gift = 'gift';
    case Seasonal = 'seasonal';

    public function toString() : string {
        return match($this) {
            self::Discount => 'Скидки',
            self::Gift => 'Подарки',
            self::Seasonal => 'Сезонные предложения',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Discount->value => 'Скидки',
            self::Gift->value => 'Подарки',
            self::Seasonal->value => 'Сезонные предложения',
        ];
    }
}
