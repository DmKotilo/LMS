<?php

namespace Order\Enums;

enum OrderDeliveryTypeEnum: string
{

    case courier = 'courier';
    case pickup = 'pickup';

    public function toString() : string {
        return match($this) {
            self::courier => 'Курьер',
            self::pickup => 'Самовывоз',
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
