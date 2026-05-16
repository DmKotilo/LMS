<?php

namespace Promotion\Database\Seeders;
use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use City\Models\City;
use Combo\Models\Combo;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Product\Models\Product;
use Promotion\Models\Promotion;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        if (Storage::disk('public')->exists('Promotion')) {
            Storage::disk('public')->deleteDirectory('Promotion');
        }
        $promotionsData = [
            [
                'name' => 'Мясная гастрономия',
                'short_description' => 'Отборное мясо — ваш секрет идеального ужина без усилий! Дарим скидку 7% на весь ассортимент до конца недели.',
                'type' => PromotionTypeEnum::Seasonal->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 7,
            ],
            [
                'name' => 'Полуфабрикаты',
                'short_description' => 'Полуфабрикаты из отборного мяса — ваш секрет идеального ужина без усилий! Дарим скидку 7% на весь ассортимент.',
                'type' => PromotionTypeEnum::Discount->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 5,
            ],
            [
                'name' => 'Мясной мерч',
                'short_description' => 'Отборное мясо — ваш секрет идеального ужина без усилий! Дарим скидку 7% на весь ассортимент до конца недели.',
                'type' => PromotionTypeEnum::Seasonal->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 4,
            ],
            [
                'name' => 'Полуфабрикаты',
                'short_description' => 'Скидки 7% на полуфабрикаты!',
                'type' => PromotionTypeEnum::Discount->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 2,
            ],
            [
                'name' => 'Подарки мясной гастрономии',
                'short_description' => 'Дарим подарки при покупке мясной гастрономии.',
                'type' => PromotionTypeEnum::Gift->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 10,
            ],
            [
                'name' => 'Сезонные предложения',
                'short_description' => 'Лучшие сезонные продукты недели со скидкой 7%!',
                'type' => PromotionTypeEnum::Seasonal->value,
                'end_date' => '2025-12-25',
                'sale_percent' =>9,
            ],
            [
                'name' => 'Мясной мерч',
                'short_description' => 'Лучшие сезонные продукты недели со скидкой 7%!',
                'type' => PromotionTypeEnum::Seasonal->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 6,
            ],
            [
                'name' => 'Полуфабрикаты',
                'short_description' => 'Специальная акция на мясной мерч!',
                'type' => PromotionTypeEnum::Gift->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 8,
            ],
            [
                'name' => 'Мясная гастрономия',
                'short_description' => 'Лучшие сезонные продукты недели со скидкой 7%!',
                'type' => PromotionTypeEnum::Seasonal->value,
                'end_date' => '2025-12-25',
                'sale_percent' => 7,
            ],
        ];
        $cities = City::active()->pluck('id')->toArray();
        foreach ($promotionsData as $data) {
            $promotion = Promotion::create($data);
            $this->attachImageToPromotion($promotion, $promotion->id);
            $products = Product::whereDoesntHave('promotion')->inRandomOrder()->take(15)->get();
            $promotion->products()->attach($products);

            $availableCombos = Combo::whereDoesntHave('promotion')->inRandomOrder()->take(2)->get();
            $promotion->combos()->attach($availableCombos);

            $randomCities = collect($cities)->random(rand(1, min(3, count($cities))));
            $promotion->cities()->attach($randomCities);
        }
    }
    protected function attachImageToPromotion(Promotion $banner, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Promotion/{$id}/image");

        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Promotion/{$banner->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $banner->id,
            'fileable_type' => Promotion::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }

}
