<?php

namespace Order\Database\Seeders;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Combo\Models\Combo;
use Illuminate\Database\Seeder;
use Order\Enums\OrderPromocodeStatusEnum;
use Order\Models\Order;
use Order\Models\OrderItem;
use Order\Models\OrderProduct;
use Order\Models\OrderPromocode;
use Product\Models\Product;
use User\Models\User;

class OrderPromocodeSeeder extends Seeder
{
    public function run(): void
    {


        //Тестовый без лимитов
        OrderPromocode::firstOrCreate([
            'promocode' => 'TEST',
            'description' => 'Тестовый без лимитов',
            'date_from' => null,
            'date_to' => null,
            'exceeded_limit' => null,
            'number_applications' => 0,
            'percent' => 10,
        ]);

        //Тестовый с лимитами
        OrderPromocode::firstOrCreate([
            'promocode' => 'TEST_LIMITED',
            'description' => 'Тестовый с лимитами',
            'date_from' => Carbon::now(),
            'date_to' => Carbon::now()->addDays(40),
            'exceeded_limit' => 10,
            'number_applications' => 0,
            'percent' => 5.5,
        ]);

        //Отключенный вручную
        OrderPromocode::firstOrCreate([
            'promocode' => 'TEST_INACTIVE',
            'description' => 'Отключенный вручную',
            'date_from' => null,
            'date_to' => null,
            'exceeded_limit' => null,
            'number_applications' => 0,
            'percent' => 10,
            'status' => OrderPromocodeStatusEnum::inactive,
        ]);

        //Истёк
        OrderPromocode::firstOrCreate([
            'promocode' => 'TEST_EXPIRED',
            'description' => 'Истёк',
            'date_from' => Carbon::now()->addDays(-40),
            'date_to' => Carbon::now()->addDays(-20),
            'exceeded_limit' => null,
            'number_applications' => 0,
            'percent' => 10,
        ]);

        //Превышен лимит использования
        OrderPromocode::firstOrCreate([
            'promocode' => 'TEST_EXCEEDED_LIMIT',
            'description' => 'Превышен лимит использования',
            'date_from' => null,
            'date_to' => null,
            'exceeded_limit' => 1000,
            'number_applications' => 1000,
            'percent' => 10,
            'status' => OrderPromocodeStatusEnum::inactive,
        ]);


    }
}
