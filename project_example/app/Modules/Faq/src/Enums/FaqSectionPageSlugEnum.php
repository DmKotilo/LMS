<?php

namespace App\Modules\Faq\src\Enums;

enum FaqSectionPageSlugEnum: string
{
    case Promo = 'promo';
    case Combo = 'combo';

    public function toString(): string
    {
        return match($this) {
            self::Promo => 'Страница промоакций',
            self::Combo => 'Страница комбо-наборов',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Promo->value => self::Promo->toString(),
            self::Combo->value => self::Combo->toString(),
        ];
    }
}
