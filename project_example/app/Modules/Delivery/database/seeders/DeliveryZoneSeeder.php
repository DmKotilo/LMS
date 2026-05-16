<?php

namespace Delivery\Database\Seeders;

use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use Cart\Models\UserCart;
use City\Models\City;
use Combo\Models\Combo;
use Delivery\Models\DeliveryZone;
use Delivery\Models\PickupLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Product\Models\Product;

class DeliveryZoneSeeder extends Seeder
{

    public function run(): void
    {
        $zonesPerCity = [
            'Москва' => [
                [
                    'name' => '1 зона',
                    'min_order_sum' => 2000,
                    'delivery_price' => 300,
                    'description' => 'внутри МКАДа',
                    'delivery_time' => 1,
                    'time_intervals' => [['from'=>'10:00','to'=>'14:00'],['from'=>'16:00','to'=>'20:00']]
                ],
                [
                    'name' => '2 зона',
                    'min_order_sum' => 2500,
                    'delivery_price' => 350,
                    'delivery_time' => 2,
                    'description' => 'за МКАД до 10 км',
                    'time_intervals' => [['from'=>'11:00','to'=>'15:00'],['from'=>'17:00','to'=>'21:00']]
                ],
                [
                    'name' => '3 зона',
                    'min_order_sum' => 4500,
                    'delivery_price' => 700,
                    'delivery_time' => 3,
                    'description' => 'за МКАД 10–20 км',
                    'time_intervals' => [['from'=>'12:00','to'=>'16:00'],['from'=>'18:00','to'=>'22:00']]
                ],
            ],
            'Санкт-Петербург' => [
                [
                    'name' => '1 зона',
                    'min_order_sum' => 1500,
                    'delivery_price' => 250,
                    'delivery_time' => 1,
                    'description' => 'Центр и Адмиралтейский район',
                    'time_intervals' => [['from'=>'10:00','to'=>'14:00'],['from'=>'16:00','to'=>'20:00']]
                ],
                [
                    'name' => '2 зона',
                    'min_order_sum' => 2000,
                    'delivery_price' => 300,
                    'delivery_time' => 2,
                    'description' => 'Василеостровский и Петроградский районы',
                    'time_intervals' => [['from'=>'11:00','to'=>'15:00'],['from'=>'17:00','to'=>'21:00']]
                ],
                [
                    'name' => '3 зона',
                    'min_order_sum' => 2500,
                    'delivery_price' => 400,
                    'delivery_time' => 2,
                    'description' => 'Зоны за КАД',
                    'time_intervals' => [['from'=>'12:00','to'=>'16:00'],['from'=>'18:00','to'=>'22:00']]
                ],
            ],
            'Сочи' => [
                [
                    'name' => '1 зона',
                    'min_order_sum' => 1500,
                    'delivery_price' => 250,
                    'delivery_time' => 1,
                    'description' => 'Центр города и Курортный район',
                    'time_intervals' => [['from'=>'09:00','to'=>'13:00'],['from'=>'14:00','to'=>'18:00']]
                ],
                [
                    'name' => '2 зона',
                    'min_order_sum' => 2500,
                    'delivery_price' => 400,
                    'delivery_time' => 2,
                    'description' => 'Адлер и Олимпийский парк',
                    'time_intervals' => [['from'=>'10:00','to'=>'14:00'],['from'=>'15:00','to'=>'19:00']]
                ],
            ],
            'Нижний Новгород' => [
                [
                    'name' => '1 зона',
                    'min_order_sum' => 1500,
                    'delivery_price' => 200,
                    'delivery_time' => 1,
                    'description' => 'Центр города',
                    'time_intervals' => [['from'=>'10:00','to'=>'14:00'],['from'=>'16:00','to'=>'20:00']]
                ],
                [
                    'name' => '2 зона',
                    'min_order_sum' => 2000,
                    'delivery_price' => 300,
                    'delivery_time' => 2,
                    'description' => 'Ленинский район',
                    'time_intervals' => [['from'=>'11:00','to'=>'15:00'],['from'=>'17:00','to'=>'21:00']]
                ],
                [
                    'name' => '3 зона',
                    'min_order_sum' => 3000,
                    'delivery_price' => 400,
                    'delivery_time' => 2,
                    'description' => 'Автозаводский район',
                    'time_intervals' => [['from'=>'12:00','to'=>'16:00'],['from'=>'18:00','to'=>'22:00']]
                ],
            ],
        ];

        foreach ($zonesPerCity as $cityName => $zones) {
            $city = City::where('name', $cityName)->first();
            if (!$city) continue;

            foreach ($zones as $zone) {
                DeliveryZone::create(array_merge($zone, ['city_id' => $city->id]));
            }
        }
    }


}
