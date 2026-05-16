<?php

namespace Notification\Enums;

enum NotificationTypeEnum: string
{

    case Promo = 'promo';
    case Novelty = 'novelty';

    public function toString() : string {
        return match($this) {
            self::Promo => 'Акция',
            self::Novelty => 'Новинка',
        };
    }

}
