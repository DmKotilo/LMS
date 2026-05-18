<?php

namespace User\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Студент',
            self::Teacher => 'Преподаватель',
            self::Administrator => 'Администратор',
        };
    }
    public function toString(): string
    {
        return match ($this) {
            self::Student => 'Студент',
            self::Teacher => 'Преподаватель',
            self::Administrator => 'Администратор',
        };
    }
}
