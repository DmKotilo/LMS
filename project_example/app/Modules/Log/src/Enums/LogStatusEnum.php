<?php

namespace Log\Enums;

enum LogStatusEnum: string
{
    case Success = 'success';
    case Badly = 'badly';

    public function toString() : string {
        return match($this) {
            self::Success => 'Успешно',
            self::Badly => 'Неудачно',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Success->value => 'Успешно',
            self::Badly->value => 'Неудачно',
        ];
    }
}
