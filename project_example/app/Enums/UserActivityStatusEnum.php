<?php

namespace App\Enums;

enum UserActivityStatusEnum: string
{
    case Success = 'success';
    case Failed = 'failed';

    public function toString(): string
    {
        return match ($this) {
            self::Success => 'Успешно',
            self::Failed => 'Неудачно',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Success->value => 'Успешно',
            self::Failed->value => 'Неудачно',
        ];
    }
}

