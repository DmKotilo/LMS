<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Combination;

use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Blog\Article\ArticleSelectionResource;
use App\MoonShine\Resources\Product\ProductResource;
use Combination\Models\Combination;
use Combination\Models\CombinationCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

use Illuminate\Support\Str;


/**
 * @extends ModelResource<Combination>
 */
class CombinationResource extends ModelResource
{
    protected string $model = Combination::class;
    protected array $with = ['categories'];
    protected string $title = 'Сочетание продуктов';

    protected function search(): array
    {
        return [
            'name',
            'product.name',
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => array(
                'style' => 'width: 200px; height: 200px;'
            )),
            Switcher::make('Активность','is_active')
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $isEditPage = $this->getItem() != null;

        if ($isEditPage){
            $localizations = Localization::where('localizationable_type', $this->getModel()::class)
                ->where('localizationable_id', $this->getItem()->id)
                ->get()
                ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
            $model = $this->getModel();
        }
        return [

        Box::make([
                ID::make(),
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       Text::make('Название','name')->required(),

                       BelongsTo::make('Продукт','product','name',ProductResource::class)
                        ->required()
                        ->placeholder('Начните вводить название...')
                        ->valuesQuery(fn (Builder $query) => $query->where('is_active', true)->orderBy('id')->limit(20))
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
                        ),



                       Switcher::make('Активность','is_active')->default(false),
                   ]),
                    Tabs\Tab::make('Описание',[
                        Textarea::make('Описание','description')->nullable(),
                        Textarea::make('Способы приготовления','cooking_method')->nullable(),
                    ]),
                    Tabs\Tab::make('Изображение',[
                        ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => array(
                            'style' => 'width: 200px; height: 200px;'
                        )),
                    ]),
                    Tab::make('Категории', [
                        Preview::make('Категории', 'categories', fn () => view('moonshine.components.categories-selected' ,
                            [
//                              'categoriesIdCheckedArray' => $this->getItem() ? $this->getItem()->combination_categories()->pluck('category_id')->toArray() : [],
                                'categoriesIdCheckedArray' => $this->getItem() ? $this->getItem()->combination_categories->pluck('category_id')->toArray() : [],
                                'defaultChecked' => $this->getItem() ? false : true,
                                'item' => $this->getItem()
                            ]))
                    ]),
                    Tabs\Tab::make('Рекомендуемые гарниры',[
                        Textarea::make('Заголовок','garnish_title')->nullable(),
                        BelongsToMany::make(
                            label: 'Гарниры',
                            relationName: 'garnishes',
                            formatted: 'name',
                            resource: GarnishResource::class
                        )->selectMode()->searchable(),
                    ]),
                    Tabs\Tab::make('Рекомендуемые соусы',[
                        Textarea::make('Заголовок','sauce_title')->nullable(),
                        BelongsToMany::make(
                            label: 'Соусы',
                            relationName: 'sauces',
                            formatted: 'name',
                            resource: SauceResource::class
                        )->selectMode()->searchable(),
                    ]),
                    Tabs\Tab::make('Рекомендуемые напитки',[
                        Textarea::make('Заголовок','drink_title')->nullable(),
                        BelongsToMany::make(
                            label: 'Напитки',
                            relationName: 'drinks',
                            formatted: 'name',
                            resource: DrinkResource::class
                        )->selectMode()->searchable(),
                    ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => $model::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),

                    Tab::make('Seo', [
                        Text::make('Title','seo_title')->nullable(),
                        Text::make('Description','seo_description')->nullable(),

                        Text::make('Ключевые слова', 'keywords')
                            ->tags(10)
                    ]),

                ]),
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),

            Text::make('Название','name')->required(),
            BelongsTo::make('Продукт','product','name',ProductResource::class)->required(),
            Switcher::make('Активность','is_active')->default(false),


            Textarea::make('Описание','description')->nullable(),
            Textarea::make('Способы приготовления','cooking_method')->nullable(),

            Text::make('Заголовок','garnish_title')->nullable(),
            BelongsToMany::make(
                label: 'Гарниры',
                relationName: 'garnishes',
                formatted: 'name',
                resource: GarnishResource::class
            )->selectMode()->searchable(),

            Text::make('Заголовок','sauce_title')->nullable(),
            BelongsToMany::make(
                label: 'Соусы',
                relationName: 'sauces',
                formatted: 'name',
                resource: SauceResource::class
            )->selectMode()->searchable(),

            Text::make('Заголовок','drink_title')->nullable(),
            BelongsToMany::make(
                label: 'Напитки',
                relationName: 'drinks',
                formatted: 'name',
                resource: DrinkResource::class
            )->selectMode()->searchable(),

        ];
    }

    /**
     * @param Combination $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */

    protected function afterCreated(mixed $item): mixed
    {
        $this->setCategories($item);
        return $item;
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
    protected function afterUpdated(mixed $item): mixed
    {
        $this->setCategories($item);

        return $item;
    }
    protected function setCategories(Combination $item): void
    {
        $categories = request()->input('categories');
        CombinationCategory::where('combination_id', $item->id)->delete();

        if (!$categories || !is_array($categories)) {
            return;
        }

        foreach ($categories as $categoryId => $checkboxSelectedOn) {
            CombinationCategory::create([
                'combination_id' => $item->id,
                'category_id' => $categoryId,
            ]);
        }
    }

    protected function rules(mixed $item): array
    {
        return [
            'image' => isset($this->getItem()->image) ? ['nullable'] : ['required'],
        ];
    }
}
