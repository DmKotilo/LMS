<?php

namespace Alert\Enums;

enum AlertTypeEnum: string
{

    case PurchaseBonus = 'purchase_bonus';
    case ReviewReply = 'review_reply';
    case CartReminder = 'cart_reminder';
    case PointsExpire = 'points_expire';

    case BirthdayBonus = 'birthday_bonus';
    case ApprovedReview = 'approved_review';
    case SubscriptionBonus = 'subscription_bonus';

    case ReferralFriend = 'referral_friend';
    case ReferralRegistration = 'referral_registration';
    case SelfPickup = 'self_pickup';
    case ManualBonus = 'manual_bonus';

    public function toString() : string {
        return match($this) {
            self::PurchaseBonus => 'Начисление бонусов',
            self::ReviewReply => 'Ответ на отзыв',
            self::CartReminder => 'Завершение покупки',
            self::PointsExpire => 'Сгорание бонусов',

            self::BirthdayBonus => 'Бонус за день рождения',
            self::ApprovedReview => 'Одобренный отзыв',
            self::SubscriptionBonus => 'Бонус за подписку',

            self::ReferralFriend => 'Бонус за приглашенного друга',
            self::ReferralRegistration => 'Бонус за регистрацию по приглашению',
            self::SelfPickup => 'Бонус за самовывоз',
            self::ManualBonus => 'Ручное начисление',
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
