<?php

namespace  App\Modules\Cart\src\Enums;

enum CartItemTypeEnum: string
{
    case Product = \Product\Models\Product::class;
    case Combo = \Combo\Models\Combo::class;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
