<?php

namespace App\Modules\Product\Product\src\Enums;

enum WeightTypeEnum: string
{
    case Kilo = 'кг';
    case Gram = 'г';

    public function toString() : string {
        return match($this) {
            self::Kilo => 'кг',
            self::Gram => 'г',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Kilo->value => 'кг',
            self::Gram->value => 'г',
        ];
    }
}
