<?php

namespace App\Modules\User\src\Enums;

enum DeleteReasonEnum:string {

    case HaveAnyAccount = 'have_any_account';
    case DataSecurity = 'data_security';
    case LotsOfAds = 'lots_of_ads';
    case Another = 'another';

    public function toString() : string {
        return match($this) {
            self::HaveAnyAccount => 'У меня есть другой аккаунт',
            self::DataSecurity => 'Меня беспокоит безопасность моих данных',
            self::LotsOfAds => 'Слишком много рекламы',
            self::Another => 'Другое',
        };
    }

    public static function toArray(): array
    {
        return [
            self::HaveAnyAccount->value => 'У меня есть другой аккаунт',
            self::DataSecurity->value => 'Меня беспокоит безопасность моих данных',
            self::LotsOfAds->value => 'Слишком много рекламы',
            self::Another->value => 'Другое',
        ];
    }



}
