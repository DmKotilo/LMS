<?php

namespace User\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum RoleEnum {

    use EnumConcern;

    case supplier;
    case manager_supplier;
    case manager_exporter;
    case admin;


    public function toString() : string {
        return match($this) {
            self::supplier => 'Поставщик',
            self::manager_supplier => 'Менеджер по закупкам',
            self::manager_exporter => 'Менеджер по продажам',
            self::admin => 'Администратор',
            default => $this->name,
        };
    }


}
