<?php

namespace Attribute\Enums;

enum InputTypeEnum : string
{
    case text = "text";
    case number = "number";
    case select = "select";
    case checkbox = "checkbox";

    public function toString() : string {
        return match($this) {
            self::text => 'Текст',
            self::number => 'Число',
            self::select => 'Выбор из списка',
            self::checkbox => 'Чекбокс',
        };
    }

    public static function toArray(): array
    {
/*        return [
            self::Pending->value => 'На проверке',
            self::Approved->value => 'Одобрено',
            self::Rejected->value => 'Отклонено',
        ];*/
    }
}
