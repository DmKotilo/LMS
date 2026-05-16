<?php

namespace Combo\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Cart\Models\UserCart;
use Combo\Models\Combo;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Product\Models\Product;

class ComboSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(10)->get();

        $combos = [
            [
                'name' => 'Лопатка + Грудинка',
                'short_description' => 'Идеально для тушения и запекания',
            ],
            [
                'name' => 'Шея + Антрекот',
                'short_description' => 'Сочное комбо для шашлыков и гриля',
            ],
            [
                'name' => 'Филе бедра + Голень',
                'short_description' => 'Набор для наваристого бульона и супа',
            ],
            [
                'name' => 'Филе грудки + Крылья',
                'short_description' => 'Лёгкий набор на каждый день',
            ],
            [
                'name' => 'Каре ягнёнка + Стейк из лопатки',
                'short_description' => 'Праздничное мясо с насыщенным вкусом',
            ],
            [
                'name' => 'Лопатка + Грудинка (2)',
                'short_description' => 'Идеально для тушения и запекания',
            ],
            [
                'name' => 'Шея + Антрекот (2)',
                'short_description' => 'Сочное комбо для шашлыков и гриля',
            ],
            [
                'name' => 'Филе бедра + Голень (2)',
                'short_description' => 'Набор для наваристого бульона и супа',
            ],
            [
                'name' => 'Филе грудки + Крылья (2)',
                'short_description' => 'Лёгкий набор на каждый день',
            ],
            [
                'name' => 'Каре ягнёнка + Стейк из лопатки (2)',
                'short_description' => 'Праздничное мясо с насыщенным вкусом',
            ],
        ];

        for ($i = 1; $i <= 9; $i++) {
            $comboData = $combos[$i - 1];

            $combo = Combo::create([
                'name' => $comboData['name'],
                'price' => 0, // Будет обновлено после привязки продуктов
                'price_type' => PriceTypeEnum::Per_Kilo,
                'weight_type' => WeightTypeEnum::Kilo,
//                'sale_price' => 1000 + $i * 350,
                'weight' => round(1.1 + $i * 0.3, 2),
                'quantity' => 1,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => "Набор из: {$comboData['name']}. Отлично подойдёт для семейного ужина или готовки на выходных.",
                'short_description' => $comboData['short_description'],
                'show_main' => rand(0,1),
            ]);

            $selectedProducts = $products->random(rand(3, 5));
            $comboProductsData = [];
            $totalPrice = 0;
            
            foreach ($selectedProducts as $product) {
                // Используем текущую цену продукта (без скидок) из атрибутов
                $productPrice = $product->attributes['price'] ?? $product->price ?? 0;
                $comboProductsData[$product->id] = ['price' => $productPrice];
                $totalPrice += $productPrice;
            }

            $combo->products()->sync($comboProductsData);
            
            // Обновляем цену комбонабора как сумму цен всех продуктов
            $combo->update(['price' => round($totalPrice, 2)]);

            $this->attachImagesToCombo($combo, $i);
        }

    }

    protected function attachImagesToCombo(Combo $combo, int $index): void
    {
        $sourcePath = public_path("images/Seeder/Combo/{$index}/image/");
        if (!file_exists($sourcePath)) {

            $id = rand(1, 3);
            $sourcePath = public_path("images/Seeder/Combo/{$id}/image/");
        }
        $files = glob("{$sourcePath}*.webp");

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $storagePath = "Combo/{$combo->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($filePath));

            File::create([
                'fileable_id' => $combo->id,
                'fileable_type' => Combo::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'images',
                'extension' => 'webp',
                'size' => filesize($filePath),
                'disk' => 'public',
            ]);
        }
    }

}
