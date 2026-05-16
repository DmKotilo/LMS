<?php

namespace Setting\Database\Seeders;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use Banner\Models\Banner;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Setting\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        Setting::firstOrCreate(
            ['key' => 'referral_reward_inviter'],
            [
                'description' => 'награда за приглашение друга после его первого заказа',
                'value' => 500,
                'value_type' => 'integer',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'referral_reward_invited'],
            [
                'description' => 'награда за первый заказ, если вы являетесь приглашенным',
                'value' => 500,
                'value_type' => 'integer',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'birthday_bonus'],
            [
                'description' => 'бонус баллов в день рождения',
                'value' => 300,
                'value_type' => 'integer',
            ]
        );


        Setting::firstOrCreate(
            ['key' => 'review_bonus'],
            [
                'description' => 'бонус за отзыв',
                'value' => 50,
                'value_type' => 'integer',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'subscribe_mails_bonus'],
            [
                'description' => 'бонус за подписку на рассылку',
                'value' => 150,
                'value_type' => 'integer',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'first_order_gift_min_order_sum'],
            [
                'description' => 'мин. сумма заказа для подарка',
                'value' => 10000,
                'value_type' => 'integer',
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'second_item_discount_percent'],
            [
                'description' => 'процент скидки на второй товар из категории',
                'value' => 10,
                'value_type' => 'integer',
            ]
        );
    }

}
