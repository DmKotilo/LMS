<?php

namespace App\Modules\Review\src\Enums;

enum ReviewReactionTypeEnum: string
{
    case Like = 'like';
    case Dislike = 'dislike';
    public function toString() : string {
        return match($this) {
            self::Like => 'Лайк',
            self::Dislike => 'Дизлайк',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Like->value => 'Лайк',
            self::Dislike->value => 'Дизлайк',
        ];
    }
}
