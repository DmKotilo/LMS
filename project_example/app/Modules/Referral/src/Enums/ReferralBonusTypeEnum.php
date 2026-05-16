<?php

namespace App\Modules\Referral\src\Enums;

enum ReferralBonusTypeEnum: string
{
    case INVITER = 'inviter'; // бонус пригласившему
    case INVITED = 'invited'; // бонус приглашённому

    public function toString() : string {
        return match($this) {
            self::INVITER => 'бонус пригласившему',
            self::INVITED => 'бонус приглашённому',
        };
    }

    public static function toArray(): array
    {
        return [
            self::INVITER->value => 'бонус пригласившему',
            self::INVITED->value => 'бонус приглашённому',
        ];
    }
}
