<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use App\MoonShine\CustomFields\ImageCustom;
use Carbon\Carbon;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Collections\Fields;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Switcher;

/**
 * @extends ModelResource<Category>
 */
class ProductCategoryResource extends ModelResource
{
    protected string $model = Category::class;

    protected string $title = 'Каталог';

    protected array $with = ['products'];

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'full_breadcrumb', fn() => $this->getItem()?->getFullBreadcrumbAttribute() ?? $this->getItem()?->name ?? ''), // Резервное значение: просто name или пустая строка
            BelongsToMany::make('Продукты','products','id',resource: ProductResource::class)->relatedLink('categories'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $modelClass = $this->getModel()::class;
        $category = $this->getItem();
        $hasParent = (bool) ($category?->parent_category_id);
        $discountHint = $hasParent
            ? 'Устанавливается у родительской категории'
            : 'Если включено, второй товар этой категории получит скидку 10%.';

        return [
            Box::make([
                Tabs::make([
                    Tab::make('Основное',[
                        ID::make()->sortable(),
                        Text::make('Название','name')->nullable(),
                        BelongsTo::make('Родитель','parent','name',ProductCategoryResource::class)
                            ->nullable()
                            ->setColumn('parent_category_id')
                            ->reactive(
                                function (Fields $fields, ?string $value): Fields {
                                    $discountField = $fields->findByColumn('has_second_item_discount');

                                    if (! $discountField) {
                                        return $fields;
                                    }

                                    if ($value) {
                                        $parentDiscount = Category::query()->find($value)?->has_second_item_discount ?? false;

                                        $discountField
                                            ->setValue($parentDiscount)
                                            ->disabled();
                                    } else {
                                        $discountField->disabled(false);
                                    }

                                    return $fields;
                                }
                            ),
                        Number::make('Порядковый номер','order')->nullable(),
                    ]),
//                    Tab::make('Описание',[
//
//                        TinyMce::make('Описание','description')->nullable(),
//                        Text::make('Объяснение','explanation')->nullable(),
//                    ]),
                    Tab::make('Фото',[
                        ImageCustom::make('Иконка','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ])->reorderable(fn($ctx) =>
                            '/file/sort/reorder-images/' . base64_encode($modelClass) . '/' . $ctx->getData()->getKey()
                        ),
                    ]),
                    Tab::make('Настройки',[
                        Slug::make('Slug','slug')->from('name')->unique(),
                        Switcher::make('Скидка -10% на второй товар', 'has_second_item_discount')
                            ->onValue(1)
                            ->offValue(0)
                            ->hint($discountHint)
                            ->disabled($hasParent),
                    ]),
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
        $modelClass = $this->getModel()::class;
        return [
            ID::make(),
            Text::make('Название','name'),
            ImageCustom::make('Иконка','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ])->reorderable(fn($ctx) =>
                '/file/sort/reorder-images/' . base64_encode($modelClass) . '/' . $ctx->getData()->getKey()
            ),
            BelongsTo::make('Родитель','parent','name',ProductCategoryResource::class),
            Number::make('Порядковый номер','order'),
            Text::make('Объяснение','explanation'),
            TinyMce::make('Описание','description'),
            Date::make('Дата создания','created_at'),
            Date::make('Дата обновления','updated_at'),

        ];
    }

    /**
     * @param Category $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
