<?php

namespace Order\Database\Seeders;
use App\Modules\Order\src\Enums\TransactionStatusEnum;
use Banner\Models\Banner;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Order\Models\Order;
use Order\Models\Transaction;
use Order\Models\TransactionMethod;

class TransactionSeeder extends Seeder
{
    protected function attachImageToMethod(TransactionMethod $method, int $id): void
    {
        $sourcePath = public_path("images/Seeder/TransactionMethod/{$id}/image");

        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "TransactionMethod/{$method->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $method->id,
            'fileable_type' => TransactionMethod::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }

    public function run(): void
    {
        if (Storage::disk('public')->exists('TransactionMethod')) {
            Storage::disk('public')->deleteDirectory('TransactionMethod');
        }
        $methodsData = [
            ['name' => 'Оплата наличными при получении', 'is_active' => true,'description' => 'Для самовывоза или курьерской доставки'],
            ['name' => 'Оплата банковской картой', 'is_active' => true,'description' => 'Visa, MasterCard, МИР, СБП'],
        ];

        foreach ($methodsData as $method) {
            $item = TransactionMethod::updateOrCreate(
                ['name' => $method['name']],
                [
                    'is_active' => $method['is_active'],
                    'description' => $method['description']
                ]
            );
            $this->attachImageToMethod($item, $item->id);
        }

        $methods = TransactionMethod::where('is_active', true)->pluck('id')->toArray();
        $orderIds = Order::pluck('id')->toArray();

/*        if (empty($methods)) {
            $this->command->info('Нет активных методов оплаты. Транзакции не будут созданы.');
            return;
        }


        $statuses = array_map(fn($case) => $case->value, TransactionStatusEnum::cases());

        for ($i = 0; $i < 50; $i++) {
            Transaction::create([
                'order_id' => $orderIds[array_rand($orderIds)],
                'transaction_method_id' => $methods[array_rand($methods)],
                'price' => round(rand(100000, 500000) / 100),
                'status' => $statuses[array_rand($statuses)],
                ]);
        }*/
    }
}
