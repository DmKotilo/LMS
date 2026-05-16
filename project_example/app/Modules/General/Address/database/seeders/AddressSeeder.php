<?php

namespace Address\Database\Seeders;
use Address\Model\Address;
use Address\Model\ContactMethod;
use App\Modules\Order\src\Enums\TransactionStatusEnum;
use City\Models\City;
use Illuminate\Database\Seeder;
use Order\Models\Order;
use Order\Models\Transaction;
use Order\Models\TransactionMethod;
use User\Models\User;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $cities = City::all();

        foreach ($users as $user) {
            $addressCount = rand(1, 3);

            for ($i = 0; $i < $addressCount; $i++) {
                $city = $cities->random();

                $addressData = [
                    'user_id' => $user->id,
                    'city_id' => $city->id,
                    'region' => 'Регион' . rand(1, 10),
                    'city_dadata' => $city->name,
                    'postal_code' => (string) rand(100000, 199999),
                    'street' => 'Улица' . rand(1, 20),
                    'house' => (string) rand(1, 50),
                    'entrance' => rand(1,5),
                    'floor' => rand(1,10),
                    'apartment' => rand(1,100),
                    'car_pass' => (bool) rand(0,1),
                    'is_primary' => $i === 0,
                    'value_dadata' => 'Улица'. rand(1, 20)."д.". rand(1, 50),
                    'final_address' => null,
                    'user_deleted' => false,
                ];

                $address = Address::create($addressData);

                $address->update([
                    'final_address' => Address::formatFinalAddress($addressData)
                ]);
            }
        }
    }
}
