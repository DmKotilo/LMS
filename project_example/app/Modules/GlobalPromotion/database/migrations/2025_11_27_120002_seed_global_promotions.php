<?php

use GlobalPromotion\Database\Seeders\GlobalPromotionSeeder;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    protected array $seededTitles = [
        '5% кэшбек для всех заказов',
        '10% кэшбек на популярные товары',
        '7% кэшбек на комбо-наборы',
        '3000 бонусов при заказе от 15 000 ₽',
        '2000 бонусов за заказ от 10 000 ₽',
    ];

    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => GlobalPromotionSeeder::class,
            '--no-interaction' => true,
        ]);
    }

    public function down(): void
    {
        GlobalPromotion::query()
            ->whereIn('title', $this->seededTitles)
            ->delete();
    }
};

