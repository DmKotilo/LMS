<?php

namespace App\Modules\Product\Product\src\Enums;

enum DegreeTypeEnum: string
{
    case Chilled = 'chilled';
    case Frozen = 'frozen';

    public function toString() : string {
        return match($this) {
            self::Chilled => 'Охлаждено',
            self::Frozen => 'Заморожено',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Chilled->value => 'Охлаждено',
            self::Frozen->value => 'Заморожено',
        ];
    }
}
