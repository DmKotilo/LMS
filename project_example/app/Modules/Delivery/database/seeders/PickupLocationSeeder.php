<?php

namespace Delivery\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use Cart\Models\UserCart;
use City\Models\City;
use Combo\Models\Combo;
use Delivery\Models\PickupLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Product\Models\Product;

class PickupLocationSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::whereIn('id', [1,2,3,4,5])->get();

        foreach ($cities as $city) {
            $count = match($city->id) {
                1 => 4, // Москва
                default => rand(1, 3),
            };

            $addresses = $this->getCityAddresses($city->id);

            for ($i = 0; $i < $count; $i++) {
                $address = $addresses[array_rand($addresses)];

                PickupLocation::create([
                    'city_id' => $city->id,
                    'title' => $this->randomTitle(),
                    'phone' => rand(0,1) ? '+7 (495) 123-45-67' : null,
                    'working_hours_from' => rand(0,1) ? '09:00' : null,
                    'working_hours_to' => rand(0,1) ? '21:00' : null,
                    'site' => rand(0,1) ? 'https://example.com' : null,
                    'delivery_time' => rand(1,3) ,
                    'dadata_json' => null,
                    'postal_code' => $address['postal_code'],
                    'region_dadata' => "Сидер Значение",
                    'city_dadata' => $city->name,
                    'street' => $address['street'],
                    'house' => $address['house'],
                    'entrance' => rand(0,1) ? (string)rand(1,10) : null,
                    'floor' => rand(0,1) ? (string)rand(1,10) : null,
                    'apartment' => rand(0,1) ? (string)rand(1,50) : null,
                    'value_dadata' => null,
                    'latitude' => $address['latitude'],
                    'longitude' => $address['longitude'],
                ]);
            }
        }
    }

    private function randomTitle(): string
    {
        $titles = [
            'Пункт выдачи №1',
            'Пункт выдачи №2',
            'Пункт выдачи №3',
            'Склад самовывоза',
            'Точка выдачи',
        ];
        return $titles[array_rand($titles)];
    }

    private function getCityAddresses(int $cityId): array
    {
        return match($cityId) {
            1 => [ // Москва
                ['street'=>'Тверская ул.', 'house'=>'7', 'postal_code'=>'125009', 'latitude'=>55.757, 'longitude'=>37.613],
                ['street'=>'Арбат', 'house'=>'13', 'postal_code'=>'119019', 'latitude'=>55.751, 'longitude'=>37.597],
                ['street'=>'Ленинградский проспект', 'house'=>'12', 'postal_code'=>'125167', 'latitude'=>55.772, 'longitude'=>37.586],
                ['street'=>'Большая Садовая ул.', 'house'=>'14', 'postal_code'=>'123001', 'latitude'=>55.764, 'longitude'=>37.605],
            ],
            2 => [ // Санкт-Петербург
                ['street'=>'Невский проспект', 'house'=>'28', 'postal_code'=>'191186', 'latitude'=>59.934, 'longitude'=>30.329],
                ['street'=>'Большая Конюшенная ул.', 'house'=>'14', 'postal_code'=>'191186', 'latitude'=>59.936, 'longitude'=>30.322],
                ['street'=>'Малая Садовая ул.', 'house'=>'6', 'postal_code'=>'191023', 'latitude'=>59.934, 'longitude'=>30.337],
            ],
            3 => [ // Сочи
                ['street'=>'Красная ул.', 'house'=>'34', 'postal_code'=>'354000', 'latitude'=>43.585, 'longitude'=>39.720],
                ['street'=>'Навагинская улица.', 'house'=>'7', 'postal_code'=>'354000', 'latitude'=>43.586, 'longitude'=>39.722],
            ],
            4 => [ // Нижний Новгород
                ['street'=>'Большая Покровская ул.', 'house'=>'12', 'postal_code'=>'603000', 'latitude'=>56.326, 'longitude'=>44.005],
                ['street'=>'Проп. Гагарина', 'house'=>'18', 'postal_code'=>'603000', 'latitude'=>56.328, 'longitude'=>44.007],
            ],
            default => [ // Другие города
                ['street'=>'Главная ул.', 'house'=>'1', 'postal_code'=>'000000', 'latitude'=>0, 'longitude'=>0],
            ],
        };
    }
}
