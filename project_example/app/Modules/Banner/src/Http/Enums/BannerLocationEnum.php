<?php

namespace App\Modules\Banner\src\Http\Enums;

enum BannerLocationEnum: string
{
    case PrivilegePage = 'privilege_page';
    case CartPage = 'cart_page';
    case MakingOrderPage = 'making_order_page';
    case ProfilePage = 'profile_page';

    public function toString(): string
    {
        return match($this) {
            self::PrivilegePage => 'Страница привилегий',
            self::CartPage => 'Корзина',
            self::MakingOrderPage => 'Оформление заказа',
            self::ProfilePage => 'Страница профиля',
        };
    }

    public static function toArray(): array
    {
        return [
            self::PrivilegePage->value => 'Страница привилегий',
            self::CartPage->value => 'Корзина',
            self::MakingOrderPage->value => 'Оформление заказа',
            self::ProfilePage->value => 'Страница профиля',
        ];
    }
}

