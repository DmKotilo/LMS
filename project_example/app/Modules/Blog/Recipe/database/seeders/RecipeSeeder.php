<?php

namespace Recipe\Database\Seeders;

use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Recipe\Models\RecipeSelection;
use Illuminate\Support\Facades\File as FileFacade;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        if (Storage::disk('public')->exists('Recipe')) {
            Storage::disk('public')->deleteDirectory('Recipe');
        }

        $categories = [
            [
                'title' => 'Говядина',
                'order_number' => 1,
                'is_displayed' => true,
            ],
            [
                'title' => 'Стейки',
                'order_number' => 2,
                'is_displayed' => true,
            ],
            [
                'title' => 'Бургеры',
                'order_number' => 3,
                'is_displayed' => true,
            ],
            [
                'title' => 'Диетические блюда',
                'order_number' => 4,
                'is_displayed' => true,
            ],
        ];

        $selections = [];
        foreach ($categories as $category) {
            $selections[$category['title']] = RecipeSelection::firstOrCreate(
                ['title' => $category['title']],
                [
                    'order_number' => $category['order_number'],
                    'is_displayed' => $category['is_displayed'],
                ]
            );
        }

        $defaultDescription = 'Сочнейшие брускетты за 20 минут. Брускетты с фланком — это вкусное и аппетитное блюдо. Фланк — это мясо с богатым ароматом и мягкой текстурой, которое отлично сочетается с хрустящим хлебом и свежими ингредиентами. Брускетты можно дополнить различными соусами, овощами или зеленью, создавая новые вкусовые сочетания. Это простое в приготовлении блюдо отлично подойдет для дружеских посиделок или семейных ужинов.';
        $defaultIngredientsDish = [
            ["title" => "Стейк фланк", "count" => "500 г"],
            ["title" => "Чиабатта", "count" => "1 шт"],
            ["title" => "Руккола", "count" => "50 г"],
            ["title" => "Цедра лимона", "count" => "1 шт"],
            ["title" => "Гранат", "count" => "1/3 шт"],
            ["title" => "Редис", "count" => "4 шт"],
        ];

        $defaultIngredientsSauce = [
            ["title" => "Желток", "count" => "2 шт"],
            ["title" => "Дижонская горчица", "count" => "1 ст.л."],
            ["title" => "Лимонный сок", "count" => "1 ч.л."],
            ["title" => "Подсолнечное масло", "count" => "75 мл"],
            ["title" => "Оливковое масло", "count" => "75 мл"],
            ["title" => "Анчоусы", "count" => "7 шт"],
        ];

        $defaultSteps = [
            ["title" => "Шаг 1", "description" => "Дать стейку 15 мин отлежаться при комнатной температуре", "images" => null],
            ["title" => "Шаг 2", "description" => "Пожарить на гриле или сковороде до прожарки medium. Дать отдохнуть 5-7 мин на доске, чтобы мясные соки распределились по всему", "images" => null], // будет заполнено позже
            ["title" => "Шаг 3", "description" => "В это время нарезать чиабатту, выложить на противень, сбрызнуть сверху оливковым маслом и поместить в заранее разогретую духовку примерно на 6 мин при умеренной температуре", "images" => null],
        ];

        $recipesData = [
            [
                'id' => 1,
                'title' => 'Запеченное мясо',
                'mini_description' => 'Нежная мраморная говядина, которую вы будете готовить на все праздники',
                'cooking_time_minutes' => 80,
                'active_cooking_time_minutes' => 20,
                'difficult' => 4,
                'number_of_persons' => 4,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 2,
                'title' => 'Сэндвич с ароматным ростбифом',
                'mini_description' => 'Изумительный и быстрый вариант для ланча или пикника',
                'cooking_time_minutes' => 15,
                'active_cooking_time_minutes' => 10,
                'difficult' => 2,
                'number_of_persons' => 4,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 3,
                'title' => 'Ростбиф',
                'mini_description' => 'Нежнейшее мясо, ради которого стоит набраться терпения',
                'cooking_time_minutes' => 40,
                'active_cooking_time_minutes' => 18,
                'difficult' => 3,
                'number_of_persons' => 4,
                'categories' => ['Говядина', 'Стейки'],
            ],
            [
                'id' => 4,
                'title' => 'Стейк рибай на гриле',
                'mini_description' => 'Сочный рибай с хрустящей корочкой и нежной серединой',
                'cooking_time_minutes' => 25,
                'active_cooking_time_minutes' => 10,
                'difficult' => 3,
                'number_of_persons' => 2,
                'categories' => ['Говядина', 'Стейки'],
            ],
            [
                'id' => 5,
                'title' => 'Тартар из говядины',
                'mini_description' => 'Классическая закуска для настоящих гурманов',
                'cooking_time_minutes' => 15,
                'active_cooking_time_minutes' => 10,
                'difficult' => 4,
                'number_of_persons' => 2,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 6,
                'title' => 'Говядина Веллингтон',
                'mini_description' => 'Праздничное блюдо, которое произведет впечатление на гостей',
                'cooking_time_minutes' => 90,
                'active_cooking_time_minutes' => 20,
                'difficult' => 5,
                'number_of_persons' => 6,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 7,
                'title' => 'Паштет из печени с коньяком',
                'mini_description' => 'Нежный паштет с утонченным ароматом коньяка',
                'cooking_time_minutes' => 50,
                'active_cooking_time_minutes' => 26,
                'difficult' => 3,
                'number_of_persons' => 4,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 8,
                'title' => 'Говяжьи щёчки томленые в вине',
                'mini_description' => 'Мягкое и ароматное мясо, которое тает во рту',
                'cooking_time_minutes' => 150,
                'active_cooking_time_minutes' => 65,
                'difficult' => 5,
                'number_of_persons' => 4,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 9,
                'title' => 'Стир-фрай с говядиной и овощами',
                'mini_description' => 'Быстрое и полезное блюдо в азиатском стиле',
                'cooking_time_minutes' => 20,
                'active_cooking_time_minutes' => 7,
                'difficult' => 2,
                'number_of_persons' => 3,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 10,
                'title' => 'Суп харчо с мраморной говядиной',
                'mini_description' => 'Настоящий грузинский суп с насыщенным мясным вкусом',
                'cooking_time_minutes' => 60,
                'active_cooking_time_minutes' => 35,
                'difficult' => 4,
                'number_of_persons' => 5,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 11,
                'title' => 'Тако с говядиной и сальсой',
                'mini_description' => 'Яркое мексиканское блюдо для вечеринок и пикников',
                'cooking_time_minutes' => 35,
                'active_cooking_time_minutes' => 15,
                'difficult' => 3,
                'number_of_persons' => 4,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 12,
                'title' => 'Говяжьи ребра BBQ',
                'mini_description' => 'Сочные ребра в сладком барбекю-соусе',
                'cooking_time_minutes' => 180,
                'active_cooking_time_minutes' => 50,
                'difficult' => 5,
                'number_of_persons' => 5,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 13,
                'title' => 'Бургер Prime с говяжьей котлетой',
                'mini_description' => 'Идеальный бургер с сочной котлетой из мраморной говядины',
                'cooking_time_minutes' => 20,
                'active_cooking_time_minutes' => 10,
                'difficult' => 2,
                'number_of_persons' => 2,
                'categories' => ['Говядина', 'Бургеры'],
            ],
            [
                'id' => 14,
                'title' => 'Тартар из говядины',
                'mini_description' => 'Классическая закуска для настоящих гурманов',
                'cooking_time_minutes' => 15,
                'active_cooking_time_minutes' => 10,
                'difficult' => 4,
                'number_of_persons' => 2,
                'categories' => ['Диетические блюда', 'Бургеры'],
            ],
            [
                'id' => 15,
                'title' => 'Говядина Веллингтон',
                'mini_description' => 'Праздничное блюдо, которое произведет впечатление на гостей',
                'cooking_time_minutes' => 90,
                'active_cooking_time_minutes' => 10,
                'difficult' => 5,
                'number_of_persons' => 6,
                'categories' => ['Говядина', 'Бургеры'],
            ],
            [
                'id' => 16,
                'title' => 'Паштет из печени с коньяком',
                'mini_description' => 'Нежный паштет с утонченным ароматом коньяка',
                'cooking_time_minutes' => 50,
                'active_cooking_time_minutes' => 10,
                'difficult' => 3,
                'number_of_persons' => 4,
                'categories' => ['Бургеры'],
            ],
            [
                'id' => 17,
                'title' => 'Говяжьи щёчки томленые в вине',
                'mini_description' => 'Мягкое и ароматное мясо, которое тает во рту',
                'cooking_time_minutes' => 150,
                'active_cooking_time_minutes' => 50,
                'difficult' => 5,
                'number_of_persons' => 4,
                'categories' => ['Говядина'],
            ],
            [
                'id' => 18,
                'title' => 'Стир-фрай с говядиной и овощами',
                'mini_description' => 'Быстрое и полезное блюдо в азиатском стиле',
                'cooking_time_minutes' => 20,
                'active_cooking_time_minutes' => 10,
                'difficult' => 2,
                'number_of_persons' => 3,
                'categories' => ['Диетические блюда', 'Бургеры'],
            ],
            [
                'id' => 19,
                'title' => 'Суп харчо с мраморной говядиной',
                'mini_description' => 'Настоящий грузинский суп с насыщенным мясным вкусом',
                'cooking_time_minutes' => 60,
                'active_cooking_time_minutes' => 23,
                'difficult' => 4,
                'number_of_persons' => 5,
                'categories' => ['Говядина', 'Бургеры'],
            ],
            [
                'id' => 20,
                'title' => 'Тако с говядиной и сальсой',
                'mini_description' => 'Яркое мексиканское блюдо для вечеринок и пикников',
                'cooking_time_minutes' => 35,
                'active_cooking_time_minutes' => 15,
                'difficult' => 3,
                'number_of_persons' => 4,
                'categories' => ['Говядина', 'Бургеры', 'Диетические блюда'],
            ],
            [
                'id' => 21,
                'title' => 'Говяжьи ребра BBQ',
                'mini_description' => 'Сочные ребра в сладком барбекю-соусе',
                'cooking_time_minutes' => 180,
                'active_cooking_time_minutes' => 50,
                'difficult' => 5,
                'number_of_persons' => 5,
                'categories' => ['Говядина', 'Диетические блюда'],
            ],
            [
                'id' => 22,
                'title' => 'Бургер Prime с говяжьей котлетой',
                'mini_description' => 'Идеальный бургер с сочной котлетой из мраморной говядины',
                'cooking_time_minutes' => 20,
                'active_cooking_time_minutes' => 10,
                'difficult' => 2,
                'number_of_persons' => 2,
                'categories' => ['Бургеры', 'Диетические блюда'],
            ],
        ];

        foreach ($recipesData as $index => $data) {
            $recipe = Recipe::create([
                'title' => $data['title'],
                'mini_description' => $data['mini_description'],
                'description' => $defaultDescription,
                'ingredients_for_dish_json' => $defaultIngredientsDish,
                'ingredients_for_sauce_json' => $defaultIngredientsSauce,
                'publish' => true,
                'show_main' => true,
                'cooking_time_minutes' => $data['cooking_time_minutes'],
                'active_cooking_time_minutes' => $data['active_cooking_time_minutes'],
                'difficult' => $data['difficult'],
                'number_of_persons' => $data['number_of_persons'],
            ]);

            foreach ($data['categories'] as $i => $categoryTitle) {
                if (isset($selections[$categoryTitle])) {
                    $recipe->selections()->attach($selections[$categoryTitle]->id, [
                        'order_number' => $i + 1,
                        'is_displayed' => true,
                    ]);
                }
            }

            $this->attachImagesToRecipe($recipe, $data['id']);

            // --- НАЧАЛО: Обновление шагов с изображением ---
            $steps = $defaultSteps;

            // Получаем список изображений рецепта
            $recipeImages = $recipe->images()->get(); // или ->pluck('file_name')->toArray()

            if ($recipeImages->count() > 0) {
                // Берём первую картинку (можно выбрать любую)
                $firstImageFile = $recipeImages->first();
                $firstImageName = $firstImageFile->file_name;

                // Прикрепляем её ко второму шагу
                if (isset($steps[1])) {
                    $steps[1]['images'] = $firstImageName;
                }
            }

            // Сохраняем шаги в поле recipe_steps_json
            $recipe->update([
                'recipe_steps_json' => $steps,
            ]);
            // --- КОНЕЦ: Обновление шагов с изображением ---
        }

        $allProducts = Product::all();

        $allRecipes = Recipe::all();

        foreach ($allProducts as $product) {
            $recipesToAttach = $allRecipes->random(rand(3, 4));

            foreach ($recipesToAttach as $recipe) {
                $recipe->products()->attach($product->id);
            }
        }
    }

    protected function attachImagesToRecipe(Recipe $recipe, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Recipe/{$id}/image/");

        if (!FileFacade::exists($sourcePath)) {
            $availablePaths = [];
            for ($i = 1; $i <= 12; $i++) {
                $altPath = public_path("images/Seeder/Recipe/{$i}/image/");
                if (FileFacade::exists($altPath)) {
                    $availablePaths[] = $altPath;
                }
            }
            if (!empty($availablePaths)) {
                $sourcePath = $availablePaths[array_rand($availablePaths)];
            } else {
                return;
            }
        }

        $files = FileFacade::files($sourcePath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $storagePath = "Recipe/{$recipe->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($file->getPathname()));

            File::create([
                'fileable_id' => $recipe->id,
                'fileable_type' => Recipe::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'images',
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
                'disk' => 'public',
            ]);
        }
    }
}
