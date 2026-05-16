<?php

namespace App\Modules\Product\Product\src\Enums;

enum PriceTypeEnum: string
{
    case Per_Kilo = 'руб/кг';
    case Per_Piece = 'руб/шт';

    public function toString() : string {
        return match($this) {
            self::Per_Kilo => 'руб/кг',
            self::Per_Piece => 'руб/шт',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Per_Kilo->value => 'руб/кг',
            self::Per_Piece->value => 'руб/шт',
        ];
    }
}
