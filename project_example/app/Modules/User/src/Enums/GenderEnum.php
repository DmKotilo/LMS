<?php

namespace User\Enums;


enum GenderEnum:string {

    case Man = 'man';
    case Woman = 'woman';

    public function toString() : string {
        return match($this) {
            self::Man => 'Мужской',
            self::Woman => 'Женский',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Man->value => 'Мужской',
            self::Woman->value => 'Женский',
        ];
    }


}
