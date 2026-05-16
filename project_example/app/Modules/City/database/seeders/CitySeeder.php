<?php

namespace City\Database\Seeders;
use City\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'uuid_1c' => '97ad2e6d-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Москва',
                'is_active' => true,
                'latitude' => 55.7558,
                'longitude' => 37.6176,
                'pickup_bonus_points' => 150,
            ],
            [
                'uuid_1c' => '97ad2e22-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Санкт-Петербург',
                'is_active' => true,
                'latitude' => 59.9343,
                'longitude' => 30.3351,
                'pickup_bonus_points' => 120,
            ],
            [
                'uuid_1c' => '97ad2d92-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Сочи',
                'is_active' => true,
                'latitude' => 43.5855,
                'longitude' => 39.7231,
                'pickup_bonus_points' => 80,
            ],
            [
                'uuid_1c' => '97ad2d8c-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Краснодар',
                'is_active' => true,
                'latitude' => 45.03954,
                'longitude' => 38.9752,
                'pickup_bonus_points' => 70,
            ],
            [
                'uuid_1c' => '91b4d0a9-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Воронеж',
                'is_active' => true,
                'latitude' => 51.66417,
                'longitude' => 39.19752,
                'pickup_bonus_points' => 60,
            ],
            [
                'uuid_1c' => '97ad2eb2-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Нижний Новгород',
                'is_active' => true,
                'latitude' => 56.3269,
                'longitude' => 44.0059,
                'pickup_bonus_points' => 90,
            ],
            [
                'uuid_1c' => '97ad2e71-0e17-11eb-bb8c-00155d0c0206',
                'name' => 'Сергиев Посад',
                'is_active' => true,
                'latitude' => 56.31364,
                'longitude' => 38.13981,
                'pickup_bonus_points' => 50,
            ],
            [
                'uuid_1c' => 'uuid-other',
                'name' => 'Другой город',
                'is_active' => true,
                'latitude' => null,
                'longitude' => null,
                'pickup_bonus_points' => 0,
            ],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name']],
                [
                    'uuid_1c' => $city['uuid_1c'],
                    'is_active' => $city['is_active'],
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                    'pickup_bonus_points' => $city['pickup_bonus_points'],
                ]
            );
        }
    }
}
