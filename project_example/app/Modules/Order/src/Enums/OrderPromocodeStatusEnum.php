<?php

namespace Order\Enums;

enum OrderPromocodeStatusEnum: string
{

    // статусы active, expired и exceeded_limit не влияют на функционал, нужны лишь для отображения в админ-панели
    // статус inactive принудительно отключает промокод

    case active = 'active';
    case inactive = 'inactive'; //отключен вручную
    case expired = 'expired'; //неактивен срок действия
    case exceeded_limit = 'exceeded_limit'; //достигнут лимит количества применений

    public function toString() : string {
        return match($this) {
            self::active => 'Активен',
            self::inactive => 'Отключен вручную',
            self::expired => 'Истёк срок действия',
            self::exceeded_limit => 'Лимит кол-ва применений',
        };
    }

    public function disabledSelect() : string {
        return match($this) {
            self::active => true,
            self::inactive => false,
            self::expired => true,
            self::exceeded_limit => true,
        };
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->name;
        }
        return $array;
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::active => 'success',
            self::inactive => 'gray',
            self::expired => 'info',
            self::exceeded_limit => 'warning',

        };
    }

/*    public static function toArray(): array
    {
        return [
            self::Pending->value => 'В обработке',
            self::Shipped->value => 'Отправлен',
            self::Delivered->value => 'Доставлен',
            self::Cancelled->value => 'Отменён',
        ];
    }*/
}
