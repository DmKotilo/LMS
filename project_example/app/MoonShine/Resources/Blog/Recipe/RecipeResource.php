<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Recipe;

use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\CustomFields\VideoCustom;
use App\MoonShine\CustomFields\VideoCustomField;
use App\MoonShine\Resources\Blog\Selection\SelectionRecipeResource;
use App\MoonShine\Resources\Blog\Selection\SelectionResource;
use App\MoonShine\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Illuminate\Support\Str;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\MorphMany;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\AssetManager\InlineJs;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Position;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use Illuminate\Database\Eloquent\Builder;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Recipe\Models\Recipe;
use MoonShine\Ui\Fields\File as FileField;


/**
 * @extends ModelResource<Recipe>
 */
class RecipeResource extends ModelResource
{
    protected string $model = Recipe::class;
    protected bool $isAsync = false  ;
    protected string $title = 'Рецепт';

    protected function search(): array
    {
        return [
            'title',
            'mini_description',
            'seo_title',
            'seo_description',
            'keywords',
        ];
    }

    protected function onLoad(): void
    {
        $this->getAssetManager()
            ->prepend(InlineJs::make(<<<'JS'
            document.addEventListener('DOMContentLoaded', () => {
                // Проверка размера для изображений
                document.querySelectorAll('input[type="file"][name="images[]"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const maxSize = 10 * 1024 * 1024;
                        for (let i = 0; i < this.files.length; i++) {
                            if (this.files[i].size > maxSize) {
                                alert('Максимальный размер загружаемого файла 10МБ');
                                this.value = '';
                                break;
                            }
                        }
                    });
                });

                // Проверка размера для видео
                document.querySelectorAll('input[type="file"][name="videos[]"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const maxSize = 10 * 1024 * 1024;
                        for (let i = 0; i < this.files.length; i++) {
                            if (this.files[i].size > maxSize) {
                                alert('Максимальный размер загружаемого файла 10МБ');
                                this.value = '';
                                break;
                            }
                        }
                    });
                });
            });
            JS
            ));
    }


    /**
     * @return FieldContract
     */


    public function indexFields(): array
    {
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображение','firstImage')
                ->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 100px; height: 100px;'
                ]),
            Text::make('Заголовок', 'title'),
            Switcher::make('Опубликован', 'publish')
                ->updateOnPreview(),
        ];
    }

    /**
     * @return FieldContract
     */
    protected function formFields(): iterable
    {
        $isEditPage = $this->getItem() != null;
        $modelClass = $this->getModel()::class;
        $model = $this->getModel();
        if ($isEditPage){
            $localizations = Localization::where('localizationable_type', $this->getModel()::class)
                ->where('localizationable_id', $this->getItem()->id)
                ->get()
                ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
        }
        return [
            Tabs::make([
                Tab::make('Основная информация', [
                    Box::make([
                        ID::make()->sortable(),
                        Text::make('Заголовок', 'title')
                            ->required(),
                        Textarea::make('Мини-описание', 'mini_description')
                            ->required(),
                        Flex::make([
                            Number::make('Время приготовления', 'cooking_time_minutes')
                                ->suffix('минуты')
                                ->required(),

                            Number::make('Активное время', 'active_cooking_time_minutes')
                                ->suffix('минуты')
                                ->required(),
                            Number::make('Сложность', 'difficult')
                                ->stars()
                                ->min(0)
                                ->max(5)
                                ->buttons()
                                ->required(),
                            Number::make('Количество персон', 'number_of_persons')
                                ->required(),
                        ]),


//                        BelongsToMany::make(
//                            label: 'Связанные продукты',
//                            relationName: 'products',
//                            formatted: fn($product): string => "$product->id | $product->name",
//                            resource: ProductResource::class,
//                        )
//                            ->columnLabel('Продукт')
//                            ->selectMode()
//                            ->searchable(),


                        BelongsToMany::make(
                            label: 'Связанные продукты',
                            relationName: 'products',
                            formatted: fn($product): string => "$product->id | $product->name",
                            resource: ProductResource::class,
                        )
                            ->columnLabel('Продукт')
                            ->selectMode()
                            ->valuesQuery(fn (Builder $query) => $query->where('is_active', true)->orderBy('id')->limit(20))

                            // Асинхронный поиск по имени или ID
                            ->asyncSearch(
                                'name',
                                searchQuery: function (Builder $query, $request, string $term, $field) {
                                    $normalizedTerm = Str::lower($term);
                                    $numericTerm = ctype_digit($term) ? (int) $term : null;

                                    return $query
                                        ->where('is_active', true)
                                        ->where(function ($searchQuery) use ($normalizedTerm, $numericTerm) {
                                            $searchQuery->whereRaw('LOWER(name) LIKE ?', ["%{$normalizedTerm}%"]);

                                            if ($numericTerm !== null) {
                                                $searchQuery->orWhere('article_number', $numericTerm);
                                            }
                                        })
                                        ->orderBy('id')
                                        ->limit(20);
                                },
                                limit: 20
                            )
                            ->placeholder('Начните вводить название...'),



                        Switcher::make('Опубликован', 'publish'),
                        Switcher::make('Показывать на главной', 'show_main'),
                    ]),
//                    MorphToMany::make(
//                        'Варианты приготовления',
//                        'variants',
//                        fn($variant): string => $variant->name,
//                        resource: RecipeVariantResource::class
//                    )->selectMode(),


                ]),
                Tab::make('Категории', [
                    BelongsToMany::make(
                        label: 'Категории',
                        relationName: 'selections',
                        formatted: 'title',
                        resource: RecipeSelectionResource::class
                    )->required()->creatable()->selectMode()->searchable(),
                ]),
                Tab::make('Описание', [
                    TinyMce::make('Описание','description')->nullable(),
                ]),

//                Tab::make('Шаги приготовления', [
//                    TinyMce::make('Шаги приготовления', 'content')
//                        ->nullable()
//                ]),

                Tab::make('Шаги приготовления', [
                    Json::make('Шаги', 'recipe_steps_json')
                    ->fields([
                        Position::make('Порядок', 'order_number'),
                        Text::make('Название', 'title'),
                        Text::make('Описание', 'description'),
                        Image::make('Изображение', 'images')->dir('Recipe/Steps')
                    ])
                ]),


                Tab::make('Seo', [
                    Text::make('Title','seo_title')->nullable(),
                    Text::make('Description','seo_description')->nullable(),

                    Text::make('Ключевые слова', 'keywords')
                        ->tags(10)
                ]),


                Tab::make('Изображения', [
                    ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                        'style' => 'width: 200px; height: 200px;'
                    ])->reorderable(fn($ctx) =>
                        '/file/sort/reorder-images/' . base64_encode($modelClass) . '/' . $ctx->getData()->getKey()
                    ),
                ]),
                Tab::make('Видео', [
                    VideoCustom::make('Видео', 'videos')
                        ->itemAttributes(fn(string $filename, int $index = 0) => [
                            'controls' => true,
                            'width' => '200',
                            'height' => '200',
                            'style' => 'object-fit: cover; display: block; margin-bottom: 10px;',
                            'preload' => 'metadata',
                        ])

                ]),

                Tab::make('Ингредиенты', [
                    Json::make('Ингредиенты для блюда', 'ingredients_for_dish_json')
                        ->fields([
                            Position::make(),
                            Text::make('Название','title'),
                            Text::make('Количество','count'),
                        ])->removable(),
                    Json::make('Ингредиенты для соуса', 'ingredients_for_sauce_json')
                        ->fields([
                            Position::make(),
                            Text::make('Название','title'),
                            Text::make('Количество','count'),
                        ])->removable()
                ]),
                Tab::make('Переводы', [
                    Preview::make('Preview', 'preview', static fn() => view('moonshine.components.translate_component', [
                        'langs' => Lang::all(),
                        'translatedFields' => Recipe::getTransaledField(),
                        'translations' => $localizations,
                    ])),
                ])->canSee(fn() => $isEditPage),

            ]),


        ];
    }

    /**
     * @return FieldContract
     */
    protected function detailFields(): iterable
    {
        $localizations = Localization::where('localizationable_type', $this->getModel()::class)
            ->where('localizationable_id', $this->getItem()->id)
            ->get()
            ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Text::make('Заголовок', 'title')
                ->required(),
            Textarea::make('Мини-описание', 'mini_description')
                ->required(),
            Switcher::make('Опубликован', 'publish'),
            Switcher::make('Показывать на главной', 'show_main'),
            Number::make('Время приготовления', 'cooking_time_minutes')
                ->suffix('минуты')
                ->required(),
            Number::make('Сложность', 'difficult')
                ->stars()
                ->min(0)
                ->max(5)
                ->buttons()
                ->required(),
            Number::make('Количество персон', 'number_of_persons')
                ->required(),

            BelongsToMany::make(
                label: 'Связанные продукты',
                relationName: 'products',
                formatted: fn($product): string => "$product->id | $product->name",
                resource: ProductResource::class,
            )
                ->columnLabel('Продукт')
                ->selectMode()
                ->searchable(),

//            MorphToMany::make(
//                'Варианты приготовления',
//                'variants',
//                fn($variant): string => $variant->name,
//                resource: RecipeVariantResource::class
//            )->selectMode(),

            BelongsToMany::make(
                label: 'Категории',
                relationName: 'selections',
                formatted: 'title',
                resource: RecipeSelectionResource::class
            ),


        ];
    }

    /**
     * @param Recipe $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
//            TODO пока убрал потому что не дает никак обновить рецепт с ошибкой "поле images обязательно", хотя картинка есть
//            'image' => isset($this->getItem()->image) ? ['nullable'] : ['required'],

            'mini_description' => ['required', 'string', 'max:255'],];
    }
    protected function beforeUpdating(mixed $item): mixed
    {
        $translations = request('translations', []);
        $modelClass = $item::class;
        $modelId = $item->id;

        foreach ($translations as $langId => $fields) {
            foreach ($fields as $field => $translate) {
                Localization::updateOrCreate(
                    [
                        'localizationable_type' => $modelClass,
                        'localizationable_id' => $modelId,
                        'lang_id' => $langId,
                        'field' => $field,
                    ],
                    [
                        'translate' => $translate ?? '',
                    ]
                );
            }
        }

        return $item;
    }
}
