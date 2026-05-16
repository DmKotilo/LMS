<?php

namespace Category\Database\Seeders;
use Category\Models\Category;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'ЧТО ПРИГОТОВИТЬ?' => [
                'Быстрый ужин',
                'Детское меню',
                'Отрубы для ростбифа',
                'Праздничный ужин',
            ],
            'WAGYU (Вагю)' => [],
            'СКИДКИ НЕДЕЛИ' => [],
            'Премиальные отрубы' => [
                'Рибай',
                'Шорт лойн',
                'Стриплойн',
                'Вырезка',
                'Ребра',
            ],
            'Dry Aged Zozulinsky&Primebeef' => [],
            'Мраморная говядина' => [
                'Шея и лопатка',
                'Грудинка',
                'Пашина',
                'Задок',
                'Голяшка',
                'Кострец',
                'Покромка',
            ],
            'Мясной Мерч' => [],
            'Рыба' => [],
            'Сыр La Petite Maison' => [],
            'Утка и цыплята' => [],
            'Мраморная говядина Халяль' => [],
            'Порционные стейки' => [],
            'Полуфабрикаты' => [],
            'Мясная гастрономия' => [],
            'Баранина' => [],
            'Грили и Аксессуары' => [
                'Наборы для BBQ',
                'Грили',
                'Перчатки для гриля',
                'Сковороды гриль',
                'Уголь для гриля',
                'Ножи',
                'Копчение',
            ],
            'Слайсеры' => [],
            'Соусы | Специи' => [
                'Перец',
                'Соль',
                'Соусы',
                'Специи',
                'Продукты из трюфелей',
            ],

            'Родитель 1' => [
                'Child Level 2',
            ],
            'Родитель 2' => [
                'First Child Level 2',
                'Second Child Level 2',
            ],
        ];
        $categoriesWithPhotos = [
            'ЧТО ПРИГОТОВИТЬ?',
            'WAGYU (Вагю)',
            'СКИДКИ НЕДЕЛИ',
            'Премиальные отрубы',
            'Dry Aged Zozulinsky&Primebeef',
            'Мраморная говядина',
            'Мясной Мерч',
            'Рыба',
            'Сыр La Petite Maison',
            'Утка и цыплята',
            'Мраморная говядина Халяль',
            'Порционные стейки',
            'Полуфабрикаты',
            'Мясная гастрономия',
            'Баранина',
            'Грили и Аксессуары',
            'Слайсеры',
            'Соусы | Специи',
        ];

        $parentIndex = 1;

        foreach ($categories as $parentName => $children) {
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_category_id' => null,
                'explanation' => null,
                'description' => null,
            ]);

            if (in_array($parentName, $categoriesWithPhotos) && $parentIndex <= 18) {
                $this->attachImageToCategory($parentCategory, $parentIndex);
                $parentIndex++;
            }

            foreach ($children as $childName) {

                $childCategory = Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_category_id' => $parentCategory->id,
                    'explanation' => null,
                    'description' => null,
                ]);

                if ($childName === 'Child Level 2') {
                    $level3 = Category::create([
                        'name' => 'SubChild Level 3',
                        'slug' => Str::slug('SubSubChild Level 3'),
                        'parent_category_id' => $childCategory->id,
                        'explanation' => null,
                        'description' => null,
                    ]);

                    Category::create([
                        'name' => 'SubSubChild Level 4',
                        'slug' => Str::slug('SubSubChild Level 4'),
                        'parent_category_id' => $level3->id,
                        'explanation' => null,
                        'description' => null,
                    ]);
                }

                if ($childName === 'First Child Level 2') {
                    Category::create([
                        'name' => 'Another SubChild Level 3',
                        'slug' => Str::slug('Another SubChild Level 3'),
                        'parent_category_id' => $childCategory->id,
                        'explanation' => null,
                        'description' => null,
                    ]);
                }
            }
        }
    }

    protected function attachImageToCategory(Category $category, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Category/{$id}/image");

        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Category/{$category->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $category->id,
            'fileable_type' => Category::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'image',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }
}
