<?php

namespace App\Modules\Order\src\Enums;

enum TransactionStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';

    public function toString(): string
    {
        return match($this) {
            self::Pending => 'В обработке',
            self::Approved => 'Подтверждена',
            self::Cancelled => 'Отменена',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Pending->value => 'В обработке',
            self::Approved->value => 'Подтверждена',
            self::Cancelled->value => 'Отменена',
        ];
    }
}
