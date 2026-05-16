<?php

namespace App\Modules\Comment\src\Enums;

enum CommentReactionTypeEnum: string
{
    case Like = 'like';
    case Dislike = 'dislike';
    
    public function toString(): string
    {
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

