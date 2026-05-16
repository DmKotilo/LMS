<?php

namespace  App\Modules\Blog\Recipe\src\Enums;

enum RecipeReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case UserDeleted = 'user_deleted';

    public function toString() : string {
        return match($this) {
            self::Pending => 'На модерации',
            self::Approved => 'Опубликован',
            self::Rejected => 'Отклонен',
            self::UserDeleted => 'Удалён пользователем',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Pending->value => 'На модерации',
            self::Approved->value => 'Опубликован',
            self::Rejected->value => 'Отклонен',
            self::UserDeleted->value => 'Удалён пользователем',
        ];
    }
}
