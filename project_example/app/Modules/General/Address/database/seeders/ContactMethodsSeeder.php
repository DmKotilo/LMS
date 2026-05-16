<?php

namespace Address\Database\Seeders;
use Address\Model\ContactMethod;
use App\Modules\Order\src\Enums\TransactionStatusEnum;
use Illuminate\Database\Seeder;
use Order\Models\Order;
use Order\Models\Transaction;
use Order\Models\TransactionMethod;

class ContactMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $methodsData = [
            ['name' => 'Связаться по телефону'],
            ['name' => 'Написать в Telegram'],
            ['name' => 'Написать в Whatsapp'],
        ];

        foreach ($methodsData as $method) {
            ContactMethod::updateOrCreate(
                ['name' => $method['name']],
            );
        }


    }
}
