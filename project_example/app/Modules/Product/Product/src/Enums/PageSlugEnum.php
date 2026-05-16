<?php

namespace App\Modules\Product\Product\src\Enums;

enum PageSlugEnum: string
{
    case Main = 'main';
    case Category = 'category';
    case Promotion = 'promotion';
    case Combo = 'combo';

    public function toString() : string {
        return match($this) {
            self::Main => 'Главная страница',
            self::Category => 'Страница категорий',
            self::Promotion => 'Страница промоакций',
            self::Combo => 'Страница комбо-наборов',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Main->value => 'Главная страница',
            self::Category->value => 'Страница категорий',
        ];
    }
}
