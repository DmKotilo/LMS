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
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\CuttingProduct;
use Product\Models\Product;
use Tag\Models\Tag;

class ProductSeeder extends Seeder
{
    protected function attachImagesToProduct(Product $product, int $id): array
    {
        $sourcePath = public_path("images/Product/{$id}/image/");

        if (!file_exists($sourcePath)) {
            $id = rand(1, 14);
            $sourcePath = public_path("images/Product/{$id}/image/");
        }

        $files = glob("{$sourcePath}{$id}*.webp");
        $previewImages = [];

        // Сортировка файлов по имени для определенности (опционально)
        sort($files);

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
                'position' => count($files) > 1 ? $index : null, // Устанавливаем position если > 1 файла
            ]);

            $previewImages[] = $storagePath;
        }

        return $previewImages;
    }

    /**
     * Привязывает видео к продукту.
     * Если в папке видео есть соответствующее превью (например, video_name_preview.webp),
     * оно также будет привязано через модель VideoPreview.
     *
     * @param Product $product
     * @param int $id ID продукта для поиска файлов
     */
    protected function attachVideosToProduct(Product $product, int $id): void
    {
        // Папка с видео для конкретного продукта (например, images/Product/1/video/)
        $videoSourcePath = public_path("images/Product/{$id}/video/");

        // Проверяем, существует ли папка с видео для этого ID
        if (!FileFacade::exists($videoSourcePath)) {
            // Если нет, можно попробовать использовать ID продукта 1 как fallback или пропустить
            // Для примера, используем ID 1, можно добавить логику выбора другого ID
            $id = 1; // Или другая логика выбора
            $videoSourcePath = public_path("images/Product/{$id}/video/");
        }

        // Проверяем снова после возможного изменения ID
        if (!FileFacade::exists($videoSourcePath)) {
            return; // Прекращаем, если папка не найдена даже для fallback
        }

        // Получаем файлы видео из папки
        $videoFiles = FileFacade::files($videoSourcePath);

        // Сортируем файлы для определенности порядка (например, по имени)
        // Это важно для корректной нумерации position
        usort($videoFiles, function ($a, $b) {
            return strnatcasecmp($a->getFilename(), $b->getFilename());
        });

        $totalVideos = count($videoFiles);

        foreach ($videoFiles as $index => $videoFile) {
            $videoFilename = $videoFile->getFilename();
            $videoExtension = $videoFile->getExtension();

            // Проверяем, является ли файл видео (простая проверка по расширению)
            if (!in_array(strtolower($videoExtension), ['mp4', 'avi', 'mov', 'webm', 'mkv'])) {
                continue; // Пропускаем, если не видео
            }

            $videoStoragePath = "Product/{$product->id}/video/{$videoFilename}";

            // Копируем видео в хранилище
            Storage::disk('public')->put($videoStoragePath, file_get_contents($videoFile->getPathname()));

            // Создаем запись для видео в таблице files
            // Устанавливаем position, если видео больше одного
            $position = $totalVideos > 1 ? $index : null;

            $videoFileRecord = File::create([
                'fileable_id' => $product->id,
                'fileable_type' => Product::class,
                'file_name' => $videoFilename,
                'path' => $videoStoragePath,
                'type_relation' => 'videos', // Указываем, что это видео
                'extension' => $videoExtension,
                'size' => $videoFile->getSize(),
                'disk' => 'public',
                'position' => $position, // Устанавливаем позицию
            ]);

            // --- Обработка превью ---
            $videoNameWithoutExt = pathinfo($videoFilename, PATHINFO_FILENAME);
            // Предположим, превью имеет имя вида: original_video_name_preview.webp
            $previewFilename = $videoNameWithoutExt . '_preview.webp'; // Имя превью
            $previewPathOnDisk = "Product/{$product->id}/video_previews/{$previewFilename}"; // Путь в storage

            $previewSourcePath = $videoSourcePath . $previewFilename;

            if (FileFacade::exists($previewSourcePath)) {
                // Если файл превью существует
                $previewStoragePath = "Product/{$product->id}/video_previews/{$previewFilename}";

                // Копируем превью в хранилище
                Storage::disk('public')->put($previewStoragePath, file_get_contents($previewSourcePath));

                // Создаем запись для превью в таблице files
                // Привязываем к продукту, устанавливаем type_relation и position (если видео > 1)
                $previewFileRecord = File::create([
                    'fileable_id' => $product->id, // Превью также привязывается к продукту
                    'fileable_type' => Product::class,
                    'file_name' => $previewFilename,
                    'path' => $previewStoragePath,
                    'type_relation' => 'video_previews', // Указываем, что это превью к видео
                    'extension' => 'webp', // или другой формат
                    'size' => filesize($previewSourcePath),
                    'disk' => 'public',
                    'position' => $position, // Устанавливаем ту же позицию, что и у видео
                ]);

                // Создаем связь между видео и превью через VideoPreview
                VideoPreview::create([
                    'video_id' => $videoFileRecord->id, // ID файла видео
                    'preview_id' => $previewFileRecord->id, // ID файла превью
                ]);

                // echo "Attached video: {$videoFilename} and preview: {$previewFilename} to product ID {$product->id} (position: " . ($position !== null ? $position : 'null') . ")\n";
            } else {
                // Если превью не найдено, просто логируем или создаем видео без превью
                // echo "Attached video: {$videoFilename} to product ID {$product->id} (position: " . ($position !== null ? $position : 'null') . ") (no preview found)\n";
            }
        }
    }

    public function run()
    {
        $tags = Tag::all();
        $brands = Brand::all();
        $attributes = Attribute::all();
        $categories = Category::all();

        if (Storage::disk('public')->exists('Product')) {
            Storage::disk('public')->deleteDirectory('Product');
        }
        if (Storage::disk('public')->exists('CuttingProduct')) {
            Storage::disk('public')->deleteDirectory('CuttingProduct');
        }
        $fishCategory = Category::firstOrCreate(['name' => 'Рыба']);

        $productsData = [
            // ... (ваш массив $productsData остается без изменений)
            [
                'id' => 1,
                'name' => 'Филей подлопаточной части "Денвер", мякоть, охлажденный (Under Blade 116G)',
                'price' => 2490,
                'weight' => 2.02,
//                'sale_price' => 2290,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Чак Ай Ролл – подлопаточный отруб, мякоть из шейного отдела туши. Альтернативные стейки с высокой мраморностью. Более жёсткое мясо, чем Рибай, но с ярким вкусом. Подходит для стейков, тушения или запекания. Рекомендуется зачищать охлаждённым при +2°C для удобства работы с плёнками.',
                'keywords' => ['бедро', 'запекание', 'мякоть', 'охлажденное мясо', 'rump roast'],
            ],
            [
                'id' => 2,
                'name' => 'Подлопаточный отруб "Чак Ай Рол" Экстра, мякоть, замороженный (Chuck Eye Roll 116D)',
                'price' => 1295,
                'weight' => 6.82,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Чак Ай Ролл – подлопаточный отруб с высокой мраморностью. Более жёсткое мясо, чем Рибай, но с насыщенным вкусом. Подходит для стейков или тушения. Зачищать рекомендуется охлаждённым при +2°C.',
                'keywords' => ['говядина', 'мраморная говядина', 'премиальное мясо'],
            ],
            [
                'id' => 3,
                'name' => 'Подлопаточный отруб "Экстра" из мрам.гов б/к охл. (Chuck Eye Roll)',
                'price' => 1295,
                'weight' => 6.71,
//                'sale_price' => 1190,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Топ Блейд – наружная часть лопатки без костей. Вторая по нежности после филе-миньон. Подходит для стейков Топ Блейд, Баттерфляй, Флэт Айрон, тартара, фарша для бургеров или шашлыка. Отличается сладковатым вкусом благодаря кукурузному откорму.',
                'keywords' => ['мясо для запекания', 'мясо для тушения', 'мясо для гриля'],
            ],
            [
                'id' => 4,
                'name' => 'Наружная часть лопатки мраморной говядины "Топ Блейд", охлажденная (Top blade, 114D)',
                'price' => 1960,
                'weight' => 2.49,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Клод – плотное мясо из задней части лопатки с насыщенным вкусом. Подходит для тушения, ростбифа, отбивных или стейков "Минутка". Рекомендуется мариновать перед жаркой. Для идеального стейка довести до 35°C, дать отдохнуть, затем до 55°C.',
                'keywords' => ['альтернативный отруб', 'primebeef', 'кусок мяса', 'маринование'],
            ],
            [
                'id' => 5,
                'name' => 'Филей подлопаточной части "Денвер", замороженный (Under Blade 116G)',
                'price' => 2496,
                'weight' => 2.01,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Стейк Денвер из лопатки, мягкий и сочный. Подходит для карпаччо, тартара, плова, бифштекса, шашлыка или бефстроганов. PRIMEBEEF – премиальная мраморная говядина с кукурузным откормом.',
                'keywords' => ['мясо в пиве', 'мясо с уксусом', 'говядина prime', 'мясо без кости'],
            ],
            [
                'id' => 6,
                'name' => 'Задняя часть лопаточного отруба "Клод", мякоть, охлажденная (Clod, 114E)',
                'price' => 1550,
                'weight' => 3.44,
//                'sale_price' => 1390,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Клодов отруб с умеренной мраморностью, подходит для стейков, медальонов, фарша или ростбифа. Требует маринования для жарки. Низкотемпературное запекание делает мясо сочным и нежным.',
                'keywords' => ['бедро', 'запекание', 'мякоть', 'охлажденное мясо', 'rump roast'],
            ],
            [
                'id' => 7,
                'name' => 'Стейк "Вегас Стрип" из мраморной говядины, мякоть, охлажденный (Vegas Strip Steak)',
                'price' => 1450,
                'weight' => 1.24,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Вырезка из лопатки, нежная и сочная, близкая по качеству к филе-миньон. Подходит для стейков, медальонов, тартара, тушения или запекания. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['говядина', 'мраморная говядина', 'премиальное мясо'],
            ],
            [
                'id' => 8,
                'name' => 'Передняя часть лопатки, мякоть, охлажденная (Chuck Tender, 116B)',
                'price' => 1190,
                'weight' => 1.38,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Вырезка из лопатки, нежная и сочная. Подходит для стейков, медальонов, тартара, тушения или запекания. Нарезать поперёк волокон под углом. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['мясо для запекания', 'мясо для тушения', 'мясо для гриля'],
            ],
            [
                'id' => 9,
                'name' => 'Вырезка из лопатки мраморной говядины, мякоть, охлажденная (Shoulder Tender, 114F)',
                'price' => 2760,
                'weight' => 1.34,
//                'sale_price' => 2590,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Мякоть шеи, жирное мясо с крупными волокнами. Подходит для тушения, запекания или жарки после маринования. Хорошо сочетается с пивными или уксусными маринадами.',
                'keywords' => ['альтернативный отруб', 'primebeef', 'кусок мяса', 'маринование'],
            ],
            [
                'id' => 10,
                'name' => 'Наружная часть бедра "Для запекания", мякоть, охлажденная (Rump Roast, 171E)',
                'price' => 1310,
                'weight' => 2.37,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Мякоть бедра, жирное мясо с крупными волокнами. Подходит для тушения, запекания или жарки после маринования. Хорошо сочетается с яркими соусами. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['мясо в пиве', 'мясо с уксусом', 'говядина prime', 'мясо без кости'],
            ],
            [
                'id' => 11,
                'name' => 'Внутренняя часть т.б отруба Огузок б/к охл. (Beef Round, Top Inside)',
                'price' => 1270,
                'weight' => 8.71,
//                'sale_price' => 1170,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Огузок – нежное и сочное мясо из внутренней части бедра. Подходит для стейков, медальонов, тартара, тушения или запекания. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['бедро', 'запекание', 'мякоть', 'охлажденное мясо', 'rump roast'],
            ],
            [
                'id' => 12,
                'name' => 'Филей наружной части бедра, мякоть, охлажденный (Eye of Round, 171C)',
                'price' => 1465,
                'weight' => 2.25,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Филей из бедра, нежное и сочное мясо. Подходит для стейков, медальонов, тартара, тушения или запекания. Нарезать поперёк волокон под углом. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['говядина', 'мраморная говядина', 'премиальное мясо'],
            ],
            [
                'id' => 13,
                'name' => 'Стейк "Паук" из мраморной говядины, мякоть, замороженный (Spider Steak)',
                'price' => 1400,
                'weight' => 1.24,
//                'sale_price' => 1280,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Стейк Паук с умеренной мраморностью. Подходит для стейков, медальонов, фарша или ростбифа. Требует маринования для жарки. Низкотемпературное запекание делает мясо сочным.',
                'keywords' => ['мясо для запекания', 'мясо для тушения', 'мясо для гриля'],
            ],
            [
                'id' => 14,
                'name' => 'Боковая часть тазобедренного отруба "Оковалок", Экстра, мякоть, охлажденная (Knuckle peeled, 167А)',
                'price' => 1380,
                'weight' => 5.42,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Оковалок – мягкий и сочный стейк из тазобедренной части. Подходит для карпаччо, тартара, плова, бифштекса, шашлыка или бефстроганов. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['альтернативный отруб', 'primebeef', 'кусок мяса', 'маринование'],
            ],
            [
                'id' => 15,
                'name' => 'Спинной отруб "Рибай Прайм", мякоть, Экстра, охлажденный (Ribeye ROLL Prime 112)',
                'price' => 6590,
                'weight' => 5.2,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Прайм – премиальный спинной отруб с высокой мраморностью. Нежное и сочное мясо, идеально для стейка ROLL Prime или ростбифа. PRIMEBEEF – мраморная говядина высшего качества.',
                'keywords' => ['рибай', 'мраморная говядина', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 16,
                'name' => 'Спинной отруб "Рибай Чойс", незачищенный, мякоть, охлажденный (Ribeye Lip-On Choice, 112A)',
                'price' => 4590,
                'weight' => 5.92,
//                'sale_price' => 3901.50,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Чойс – спинной отруб с насыщенным вкусом и мраморностью. Подходит для стейков или запекания. Требует минимальной зачистки. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'охлажденное мясо', 'стейк'],
            ],
            [
                'id' => 17,
                'name' => '1/2 Спинного отруба "Рибай Топ Чойс", мякоть, Экстра, охлажденный (Ribeye Roll, Top Choice)',
                'price' => 5840,
                'weight' => 2.74,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Половина спинного отруба Рибай Топ Чойс с высокой мраморностью. Идеально для стейков или запекания. Нежное и сочное мясо. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 18,
                'name' => 'Спинной отруб "Рибай Топ Чойс на кости", Экстра, охлажденный (Ribeye, Roast-Ready Top Choice)',
                'price' => 5290,
                'weight' => 8.03,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Топ Чойс на кости – сочное мясо с насыщенным вкусом. Подходит для запекания или стейков на гриле. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['рибай', 'мясо на кости', 'охлажденное мясо', 'стейк'],
            ],
            [
                'id' => 19,
                'name' => 'Спинной отруб "Рибай Топ Чойс Экстра", мякоть, охлажденный (Ribeye, ROLL Top Choice 112)',
                'price' => 5950,
                'weight' => 5.13,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Топ Чойс Экстра – нежное и мраморное мясо для стейков или ростбифа. Высокая мраморность обеспечивает насыщенный вкус. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 20,
                'name' => 'Спинной отруб "Рибай Селект", незачищенный, мякоть, охлажденный (Ribeye Lip-On Select)',
                'price' => 2880,
                'weight' => 5.42,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Селект – спинной отруб с умеренной мраморностью. Подходит для стейков или запекания. Требует минимальной зачистки. PRIMEBEEF – мраморная говядина.',
                'keywords' => ['рибай', 'охлажденное мясо', 'стейк', 'говядина'],
            ],
            [
                'id' => 21,
                'name' => 'Спинной отруб "Рибай Прайм на кости", Экстра, охлажденный (Ribeye, Roast-Ready Prime, 109D)',
                'price' => 5990,
                'weight' => 8.56,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Прайм на кости – премиальное мясо с высокой мраморностью. Идеально для запекания или стейков на гриле. PRIMEBEEF – мраморная говядина высшего качества.',
                'keywords' => ['рибай', 'мясо на кости', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 22,
                'name' => 'Спинной отруб незачищенный б/к охл. (Рибай Топ Чойс/Ribeye, Lip-On)',
                'price' => 5690,
                'weight' => 5.84,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Топ Чойс – незачищенный спинной отруб с насыщенным вкусом. Подходит для стейков или запекания. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'охлажденное мясо', 'стейк'],
            ],
            [
                'id' => 23,
                'name' => 'Спинной отруб незачищенный б/к охл. (Рибай Прайм/Ribeye Lip-On Prime)',
                'price' => 5790,
                'weight' => 5.87,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Прайм – незачищенный спинной отруб с высокой мраморностью. Идеально для стейков или ростбифа. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 24,
                'name' => 'Рибай Топ Чойс ЭКСПОРТ, незачищенный, замороженный (Ribeye Top Choice Lip-on)',
                'price' => 4850,
                'weight' => 4.17,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Топ Чойс ЭКСПОРТ – замороженный спинной отруб с насыщенным вкусом. Подходит для стейков или запекания. PRIMEBEEF – мраморная говядина.',
                'keywords' => ['рибай', 'замороженное мясо', 'стейк', 'говядина'],
            ],
            [
                'id' => 25,
                'name' => '1/2 Спинного отруба "Рибай Чойс", незачищенный, мякоть, охлажденный (Ribeye Lip-On Choice)',
                'price' => 5120,
                'weight' => 2.82,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Половина спинного отруба Рибай Чойс – нежное мясо с умеренной мраморностью. Подходит для стейков или запекания. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'охлажденное мясо', 'стейк', 'говядина'],
            ],
            [
                'id' => 26,
                'name' => 'Спинной отруб "Томагавк", 3-4 ребра, Экстра, охлажденный (Ribeye, Roast-Ready)',
                'price' => 5910,
                'weight' => 5.34,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Томагавк – эффектный спинной отруб на 3-4 ребра с высокой мраморностью. Идеально для запекания или стейков на гриле. PRIMEBEEF – премиальная говядина.',
                'keywords' => ['рибай', 'мясо на кости', 'премиальное мясо', 'стейк'],
            ],
            [
                'id' => 27,
                'name' => '1/2 Рибай Чойс, незачищенный (Ribeye Lip On)',
                'price' => 5120,
                'weight' => 3.15,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Половина Рибай Чойс – незачищенное мясо с насыщенным вкусом. Подходит для стейков или запекания. PRIMEBEEF – мраморная говядина.',
                'keywords' => ['рибай', 'охлажденное мясо', 'стейк', 'говядина'],
            ],
            [
                'id' => 28,
                'name' => 'Спинной отруб "Экстра" н/к охл. (Ribeye, Roast-Ready Choice)',
                'price' => 3927,
                'weight' => 6.26,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай Экстра – спинной отруб с умеренной мраморностью. Подходит для запекания или стейков. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['рибай', 'охлажденное мясо', 'стейк', 'говядина'],
            ],
            [
                'id' => 29,
                'name' => 'Спинной отруб из мяса говядины б/к охл. (Ribeye/Рибай)',
                'price' => 2480.89,
                'weight' => 4.62,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Рибай – классический спинной отруб без кости с насыщенным вкусом. Подходит для стейков или запекания. PRIMEBEEF – премиальная мраморная говядина.',
                'keywords' => ['рибай', 'мраморная говядина', 'охлажденное мясо', 'стейк'],
            ],
            [
                'id' => 30,
                'name' => 'Форель в соусе Терияки 250 г. (10 суток)',
                'price' => 1049,
                'weight' => 0.25,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Форель в соусе Терияки – нежная рыба с пикантным вкусом. Готова к приготовлению, идеальна для запекания или жарки. Срок хранения – 10 суток.',
                'keywords' => ['форель', 'рыба', 'терияки', 'готовое блюдо'],
            ],
            [
                'id' => 31,
                'name' => 'Форель в соусе «по-тайски» 250 г. (10 суток)',
                'price' => 1049,
                'weight' => 0.25,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Форель в соусе по-тайски – рыба с ярким азиатским вкусом. Подходит для запекания или жарки. Срок хранения – 10 суток.',
                'keywords' => ['форель', 'рыба', 'азиатская кухня', 'готовое блюдо'],
            ],
            [
                'id' => 32,
                'name' => 'Форель в соусе «по-французски» 250 г. (10 суток)',
                'price' => 1049, // Исправлено с 10449 на 1049 (вероятно, опечатка в предыдущем коде)
                'weight' => 0.25,
//                'sale_price' => null,
                'article_number' => fake()->unique()->numerify('#####'),
                'description' => 'Форель в соусе по-французски – нежная рыба с изысканным вкусом. Идеальна для запекания или жарки. Срок хранения – 10 суток.',
                'keywords' => ['форель', 'рыба', 'французская кухня', 'готовое блюдо'],
            ],
        ];

        $leafCategories = Category::whereDoesntHave('children')->get();

        foreach ($leafCategories as $category) {

            if ($category->name === 'Рыба') {
                $selectedProducts = array_filter($productsData, fn($product) => in_array($product['id'], [30, 31, 32]));
            } else {
                $selectedProducts = array_filter($productsData, fn($product) => !in_array($product['id'], [30, 31, 32]));
                $selectedProducts = array_values($selectedProducts);

                if (count($selectedProducts) > 0) {
                    $randomKeys = array_rand($selectedProducts, rand(2, min(3, count($selectedProducts))));
                    $selectedProducts = array_map(fn($key) => $selectedProducts[$key], (array)$randomKeys);
                } else {
                    continue;
                }
            }

            foreach ($selectedProducts as $productData) {
                $priceType = fake()->randomElement(PriceTypeEnum::cases());
                $weeksAgo = rand(0, 10);
                $createdAt = now()->subWeeks($weeksAgo)->startOfWeek()->addDays(rand(0, 6))->setTime(rand(0, 23), rand(0, 59), rand(0, 59));

                $slug = Str::slug($productData['name']);

                $product = Product::create([
                    'name' => $productData['name'],
                    'price' => $productData['price'],
                    'price_type' => in_array($productData['id'], [30, 31, 32]) ? PriceTypeEnum::Per_Piece : PriceTypeEnum::Per_Kilo,
                    'weight_type' => in_array($productData['id'], [30, 31, 32]) ? null : WeightTypeEnum::Kilo,
//                    'sale_price' => $productData['sale_price'],
                    'article_number' => $productData['article_number'],
                    'weight' => $productData['weight'],
//                    'quantity' => 1,
                    'description' => $productData['description'],
                    'short_description' => 'Короткое описание',
                    'keywords' => implode(',', $productData['keywords']),
                    'manufacturer_id' => rand(1,2),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'is_new' => rand(0,1),
                    'degree_type' => fake()->randomElement(['frozen', 'chilled', ]),
                    'slug' => $slug,

                ]);
                foreach (City::all() as $city) {
                    CityProductCount::create([
                        'city_id' => $city->id,
                        'product_id' => $product->id,
                        'quantity' => rand(0, 100),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
                $categoryIds = [];
                $currentCategory = $category;

                while ($currentCategory) {
                    $categoryIds[] = $currentCategory->id;
                    $currentCategory = $currentCategory->parent;
                }

                $children = $category->children;

                if ($children->isNotEmpty()) {
                    $randomChild = $children->random();
                    $categoryIds[] = $randomChild->id;

                    $currentChild = $randomChild->children()->first();

                    while ($currentChild) {
                        $categoryIds[] = $currentChild->id;
                        $currentChild = $currentChild->children()->first();
                    }
                }

                $product->categories()->attach(array_unique($categoryIds));



                $product->tags()->attach(
                    $tags->random(min(3, $tags->count()))->pluck('id')
                );
                $product->brands()->attach(
                    $brands->random(min(1, $brands->count()))->pluck('id')
                );
                $selectedAttributes = $attributes->random(min(6, $attributes->count()));

                $cut = CuttingProduct::create([
                    'product_id' => $product->id,
                    'content' => '<h4 class="text-default-medium md:text-default-medium">Особенности разделки спинного отруба «Рибай Прайм»</h4>
                                                          <p class="text-subsidiary-reg md:text-mob-small-reg mt-4">
                                                            Наше предприятие — это синергия традиций и инноваций. Вот что делает его уникальным: Собственный комбикормовый цех (10 000 тонн
                                                            в год) — корма создаются из натуральных ингредиентов, без единой химической добавки.
                                                          </p>
                                                          <p class="text-subsidiary-reg md:text-mob-small-reg mt-3">
                                                            Склады для хранения зерна (17 000 м³) — запас сырья на год вперед гарантирует бесперебойное производство даже в условиях
                                                            сезонных колебаний. Современная линия переработки (5000 тонн продукции ежегодно) — автоматизированные процессы и
                                                            многоступенчатый контроль качества исключают риск загрязнений. Родительское стадо французской селекции — генетика, проверенная
                                                            временем, обеспечивает стабильные характеристики мяса: сочность, мраморность и нежный вкус.
                                                          </p>',
                    'button_link' => '#',
                    'button_text' => 'Узнать подробнее',
                    'video_link' => 'https://rutube.ru/play/embed/c882d7e2f60840df82ff6d3514307726    ',
                ]);
                $this->attachImagesToCutting($cut, $cut['id']);

                $syncData = [];
                foreach ($selectedAttributes as $attribute) {
                    $value = null;
                    if ($attribute->input_type === InputTypeEnum::select && is_array($attribute->options)) {
                        $value = fake()->randomElement($attribute->options);
                    } elseif ($attribute->input_type === InputTypeEnum::number) {
                        $value = fake()->randomFloat(2, 0.1, 50);
                    } else {
                        $value = fake()->words(2, true);
                    }
                    $syncData[$attribute->id] = ['value' => $value];
                }

                $product->attributes()->attach($syncData);

                // --- Вызов метода для привязки видео (только для ID 10 и 11) ---
//                if ($productData['id'] == 10 || $productData['id'] == 11) {
//                    $this->attachVideosToProduct($product, $productData['id']);
//                }

                // Привязка видео отключена
                // $videoSourcePath = public_path("images/Product/{$productData['id']}/video/");
                // if (FileFacade::exists($videoSourcePath) && count(FileFacade::files($videoSourcePath)) > 0) {
                //     $this->attachVideosToProduct($product, $productData['id']);
                // }

                $previewImages = $this->attachImagesToProduct($product, $productData['id']);

                $product->update([
                    'preview_images' => $previewImages,
                ]);

                $relatedIds = Product::where('id', '!=', $product->id)
                    ->inRandomOrder()
                    ->limit(rand(4, 7))
                    ->pluck('id');

                $product->relatedProducts()->syncWithoutDetaching($relatedIds);

            }
        }
    }

    protected function attachImagesToCutting(CuttingProduct $cut, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Cutting/{$id}/image/");

        if (!FileFacade::exists($sourcePath)) {
            for ($i = 1; $i <= 2; $i++) {
                $altPath = public_path("images/Seeder/Cutting/{$i}/image/");
                if (FileFacade::exists($altPath)) {
                    $sourcePath = $altPath;
                    break;
                }
            }
        }

        $files = FileFacade::files($sourcePath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            $storagePath = "CuttingProduct/{$cut->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($file->getPathname()));

            File::create([
                'fileable_id' => $cut->id,
                'fileable_type' => CuttingProduct::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'image',
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
                'disk' => 'public',
            ]);
        }
    }
}
