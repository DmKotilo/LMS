<?php

namespace App\Modules\Review\src\Enums;

enum ReviewStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case UserDeleted = 'user_deleted';

    public function toString() : string {
        return match($this) {
            self::Pending => 'На модерации',
            self::Approved => 'Активен',
            self::Rejected => 'Отклонён',
            self::UserDeleted => 'Удалён пользователем',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Pending->value => 'На модерации',
            self::Approved->value => 'Активен',
            self::Rejected->value => 'Отклонён',
            self::UserDeleted->value => 'Удалён пользователем',
        ];
    }
}
