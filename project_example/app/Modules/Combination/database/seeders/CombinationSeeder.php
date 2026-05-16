<?php

namespace Combination\Database\Seeders;
use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use Category\Models\Category;
use City\Models\City;
use Combination\Models\Combination;
use Combination\Models\Drink;
use Combination\Models\Garnish;
use Combination\Models\Sauce;
use Combo\Models\Combo;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Product\Models\Product;
use Promotion\Models\Promotion;

class CombinationSeeder extends Seeder
{
    public function run(): void
    {
        if (Storage::disk('public')->exists('Combination')) {
            Storage::disk('public')->deleteDirectory('Combination');
        }
        if (Storage::disk('public')->exists('Sauce')) {
            Storage::disk('public')->deleteDirectory('Sauce');
        }
        if (Storage::disk('public')->exists('Drink')) {
            Storage::disk('public')->deleteDirectory('Drink');
        }
        if (Storage::disk('public')->exists('Garnish')) {
            Storage::disk('public')->deleteDirectory('Garnish');
        }
        $categories = Category::query()->get();

        $products = Product::query()->get();

        $garnishes = collect([
            'Картофель фри',
            'Рис отварной',
            'Овощи на гриле',
            'Пюре картофельное',
            'Паста с сыром',
        ])->map(function ($name, $index) {
            $garnish = Garnish::create([
                'name' => $name,
                'description' => "Гарнир: {$name}",
            ]);

            $this->attachImageToGarnish($garnish, $index + 1);

            return $garnish;
        });

        $garnishes->each(function ($garnish) use ($products) {
            $garnish->products()->attach($products->random(rand(2, 3))->pluck('id'));
        });

        $sauces = collect([
            'Сырный соус',
            'Барбекю',
            'Чесночный соус',
            'Кисло-сладкий',
            'Грибной соус',
        ])->map(function ($name, $index) {
            $sauce = Sauce::create([
                'name' => $name,
                'description' => "Соус: {$name}",
            ]);

            $this->attachImageToSauce($sauce, $index + 1);

            return $sauce;
        });

        $sauces->each(function ($sauce) use ($products) {
            $sauce->products()->attach($products->random(rand(2, 3))->pluck('id'));
        });

        $drinks = collect([
            'Кола',
            'Сок апельсиновый',
            'Минеральная вода',
            'Чай черный',
            'Кофе американо',
        ])->map(function ($name, $index) {
            $drink = Drink::create([
                'name' => $name,
                'description' => "Напиток: {$name}",
            ]);

            // вызываем функцию для добавления изображения
            $this->attachImageToDrink($drink, $index + 1);

            return $drink;
        });


        $categories->each(function ($category) use ($products, $garnishes, $sauces, $drinks) {

            $combinationCount = rand(1, 4);

            for ($i = 1; $i <= $combinationCount; $i++) {
                $combination = Combination::create([
                    'name' => "{$products[rand(1,50)]->name}",
                    'description' => "Идеальное сочетание из категории {$category->name}",
                    'cooking_method' => 'Приготовить и подать горячим.',
                    'product_id' => $products->random()->id,
                    'sauce_title' => "Выбирайте соус по настроению и предпочтениям — каждый из них подчеркнет уникальность вашего стейка 'Рибай' и сделает трапезу еще более приятной!",
                    'garnish_title' => "Мы подобрали несколько вариантов гарнира, которые подчеркнут вкус и сделают ваше блюдо еще более изысканным.",
                    'drink_title' => "Выбирайте напиток по настроению и предпочтениям — правильный выбор сделает ваш ужин с 'Рибаем' незабываемым!",
                    'is_active' => rand(0,1),
                ]);
                $this->attachImageToCombination($combination, $combination->id);

                $combination->categories()->attach($category->id);

                $combination->garnishes()->attach($garnishes->random(rand(1, 2))->pluck('id'));
                $combination->sauces()->attach($sauces->random(rand(1, 2))->pluck('id'));
                $combination->drinks()->attach($drinks->random(rand(1, 2))->pluck('id'));
            }
        });

    }


    protected function attachImageToCombination(Combination $combination, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Combination/{$id}/image");

        if (!file_exists($sourcePath)) {

            $id = rand(1, 14);
            $sourcePath = public_path("images/Seeder/Combination/{$id}/image/");
        }

        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Combination/{$combination->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $combination->id,
            'fileable_type' => Combination::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }
    protected function attachImageToSauce(Sauce $sauce, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Sauce/{$id}/image");
        if (!file_exists($sourcePath)) {

            $id = rand(1, 6);
            $sourcePath = public_path("images/Seeder/Sauce/{$id}/image/");
        }
        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Sauce/{$sauce->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $sauce->id,
            'fileable_type' => Sauce::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }
    protected function attachImageToDrink(Drink $drink, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Drink/{$id}/image");
        if (!file_exists($sourcePath)) {

            $id = rand(1, 8);
            $sourcePath = public_path("images/Seeder/Drink/{$id}/image/");
        }
        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Drink/{$drink->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $drink->id,
            'fileable_type' => Drink::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }
    protected function attachImageToGarnish(Garnish $garnish, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Garnish/{$id}/image");
        if (!file_exists($sourcePath)) {

            $id = rand(1, 8);
            $sourcePath = public_path("images/Seeder/Garnish/{$id}/image/");
        }
        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Garnish/{$garnish->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $garnish->id,
            'fileable_type' => Garnish::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }

}
