<?php

namespace App\Modules\ContactForm\src\Http\Enums;

enum ContactFormStatusEnum: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function toString() : string {
        return match($this) {
            self::New => 'Новая',
            self::InProgress => 'В обработке',
            self::Completed => 'Выполнена',

        };
    }

    public static function toArray(): array
    {
        return [
            self::New->value => 'Новая',
            self::InProgress->value => 'В обработке',
            self::Completed->value => 'Выполнена',

        ];
    }
}
