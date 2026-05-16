<?php

namespace User\Enums;

enum EducationForm: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Очная',
            self::PartTime => 'Заочная',
        };
    }
}
