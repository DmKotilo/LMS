<?php

namespace  App\Modules\WhiteList\List\src\Enums;
enum WhitelistItemTypeEnum: string
{
    case Product = \Product\Models\Product::class;
    case Recipe = \Recipe\Models\Recipe::class;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
