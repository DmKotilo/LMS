<?php

namespace Product\Database\Seeders;

use App\Modules\City\src\Models\CityProductCount;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Brand\Models\Brand;
use City\Models\City;
use Illuminate\Support\Facades\File as FileFacade;
use Category\Models\Category;
use File\Models\Files\File;
use File\Models\VideoPreview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Product\Models\CuttingProduct;
use Product\Models\Product;
use Tag\Models\Tag;
use Illuminate\Support\Facades\DB;

class MoreProductSeeder extends Seeder
{
    protected function attachImagesToProduct(Product $product, int $fallbackId = 1): array
    {
        $sourcePath = public_path("images/Product/{$fallbackId}/image/");
        if (!FileFacade::exists($sourcePath)) {
            // Если даже fallback нет — возвращаем пусто
            return [];
        }

        $files = glob("{$sourcePath}*.webp");
        if (empty($files)) return [];

        sort($files);
        $previewImages = [];

        foreach ($files as $index => $filePath) {
            $filename = basename($filePath);
            $storagePath = "Product/{$product->id}/image/{$filename}";
            Storage::disk('public')->put($storagePath, file_get_contents($filePath));

            File::create([
                'fileable_id' => $product->id,
                'fileable_type' => Product::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'images',
                'extension' => 'webp',
                'size' => filesize($filePath),
                'disk' => 'public',
                'position' => count($files) > 1 ? $index : null,
            ]);

            $previewImages[] = $storagePath;
        }

        return $previewImages;
    }

    protected function attachVideosToProduct(Product $product, int $sourceId): void
    {
        $videoSourcePath = public_path("images/Product/{$sourceId}/video/");
        if (!FileFacade::exists($videoSourcePath)) {
            return;
        }

        $videoFiles = FileFacade::files($videoSourcePath);
        usort($videoFiles, fn($a, $b) => strnatcasecmp($a->getFilename(), $b->getFilename()));
        $totalVideos = count($videoFiles);

        foreach ($videoFiles as $index => $videoFile) {
            $ext = strtolower($videoFile->getExtension());
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv'])) continue;

            $filename = $videoFile->getFilename();
            $storagePath = "Product/{$product->id}/video/{$filename}";
            Storage::disk('public')->put($storagePath, file_get_contents($videoFile->getPathname()));

            $position = $totalVideos > 1 ? $index : null;
            $videoRecord = File::create([
                'fileable_id' => $product->id,
                'fileable_type' => Product::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'videos',
                'extension' => $ext,
                'size' => $videoFile->getSize(),
                'disk' => 'public',
                'position' => $position,
            ]);

            // Превью
            $previewName = pathinfo($filename, PATHINFO_FILENAME) . '_preview.webp';
            $previewSource = $videoSourcePath . $previewName;

            if (FileFacade::exists($previewSource)) {
                $previewStorage = "Product/{$product->id}/video_previews/{$previewName}";
                Storage::disk('public')->put($previewStorage, file_get_contents($previewSource));

                $previewRecord = File::create([
                    'fileable_id' => $product->id,
                    'fileable_type' => Product::class,
                    'file_name' => $previewName,
                    'path' => $previewStorage,
                    'type_relation' => 'video_previews',
                    'extension' => 'webp',
                    'size' => filesize($previewSource),
                    'disk' => 'public',
                    'position' => $position,
                ]);

                VideoPreview::create([
                    'video_id' => $videoRecord->id,
                    'preview_id' => $previewRecord->id,
                ]);
            }
        }
    }

    public function run()
    {
        // === 1. Очистка связанных данных ===
        // Проверяем существование таблиц перед удалением
        $tables = [
            'city_product_counts',
            'files',
            'product_category',
            'product_tag',
            'product_brand',
            'product_attribute'
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // Удаляем продукты
        Product::query()->delete();

        // === 2. Очистка файлов ===
        if (Storage::disk('public')->exists('Product')) {
            Storage::disk('public')->deleteDirectory('Product');
        }

        // === 3. Подготовка данных ===
        $tags = Tag::pluck('id')->all();
        $brands = Brand::pluck('id')->all();
        $attributes = Attribute::all();
        $cities = City::pluck('id')->all();

        $fishCategory = Category::firstOrCreate(['name' => 'Рыба']);
        $meatCategories = Category::where('id', '!=', $fishCategory->id)
            ->whereDoesntHave('children')
            ->pluck('id')
            ->all();

        // === 4. Генерация товаров ===
        $totalProducts = 2000;
        $fishCount = 50;

        foreach (range(1, $totalProducts) as $i) {
            $isFish = $i <= $fishCount;
            $now = now();

            $product = Product::create([
                'name' => $isFish
                    ? "Форель в соусе №{$i} (тест)"
                    : "Говядина Рибай №{$i} (тест)",
                'price' => rand(990, 9990),
                'price_type' => $isFish ? PriceTypeEnum::Per_Piece : PriceTypeEnum::Per_Kilo,
                'weight_type' => $isFish ? null : WeightTypeEnum::Kilo,
                'article_number' => str_pad($i, 6, '0', STR_PAD_LEFT),
                'weight' => $isFish ? 0.25 : round(rand(10, 100) / 10, 2),
                'description' => "Тестовое описание для товара №{$i}. PRIMEBEEF — премиальная говядина.",
                'short_description' => 'Тестовый товар',
                'keywords' => implode(',', ['тест', 'товар', "id_{$i}"]),
//                'manufacturer_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'is_new' => rand(0, 1),
                'degree_type' => rand(0, 1) ? 'frozen' : 'chilled',
            ]);

            // Категории - проверяем существование таблицы
            if (DB::getSchemaBuilder()->hasTable('product_category')) {
                if ($isFish) {
                    $product->categories()->attach($fishCategory->id);
                } elseif (!empty($meatCategories)) {
                    $product->categories()->attach($meatCategories[array_rand($meatCategories)]);
                }
            }

            // Теги и бренды - проверяем существование таблиц
            if (DB::getSchemaBuilder()->hasTable('product_tag') && !empty($tags)) {
                $product->tags()->attach($tags[array_rand($tags)]);
            }

            if (DB::getSchemaBuilder()->hasTable('product_brand') && !empty($brands)) {
                $product->brands()->attach($brands[array_rand($brands)]);
            }

            // Атрибуты - проверяем существование таблицы
            if (DB::getSchemaBuilder()->hasTable('product_attribute') && $attributes->isNotEmpty()) {
                $selected = $attributes->random(min(3, $attributes->count()));
                $sync = [];
                foreach ($selected as $attr) {
                    $value = match ($attr->input_type) {
                        InputTypeEnum::select => $attr->options ? fake()->randomElement($attr->options) : 'опция',
                        InputTypeEnum::number => fake()->randomFloat(2, 0.1, 50),
                        default => fake()->words(2, true),
                    };
                    $sync[$attr->id] = ['value' => $value];
                }
                $product->attributes()->attach($sync);
            }

            // Города
            foreach ($cities as $cityId) {
                CityProductCount::create([
                    'city_id' => $cityId,
                    'product_id' => $product->id,
                    'quantity' => rand(0, 100),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Видео — только для первых 5 товаров
            if ($i <= 5) {
                $this->attachVideosToProduct($product, 15);
            }

            // Изображения
            $previewImages = $this->attachImagesToProduct($product, 1);
            if (!empty($previewImages)) {
                $product->update(['preview_images' => $previewImages]);
            }
        }

        $this->command->info("✅ Создано {$totalProducts} товаров.");
    }
}
