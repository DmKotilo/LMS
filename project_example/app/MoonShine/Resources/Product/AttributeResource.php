<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use App\Livewire\Moonshine\Components\CategoriesSelected;
use App\View\Moonshine\CategoriesSelectedView;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Attribute\Models\Attribute as AttributeProduct;
use Attribute\Models\AttributeCategory;
use Attribute\Models\ProductAttribute;
use Carbon\Carbon;
use Category\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Product\Models\Product;

/**
 * @extends ModelResource<Attribute>
 */
class AttributeResource extends ModelResource
{
    protected string $model = Attribute::class;

    protected string $title = 'Характеристики продукта';

    protected bool $isAsync = false;

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Enum::make('Тип поля','input_type')->attach(InputTypeEnum::class),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */


/*$table->string('name')->unique();
$table->string('input_type')->nullable(); //bool / text / select / number
$table->string('value_type')->nullable(); //json для текста
$table->string('Str::slug')->unique();
$table->string('options')->nullable(); //json для текста
$table->boolean('is_default')->nullable(); //неудаляемая характеристика
    */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make('Основное', [
                        ID::make(),
                        Switcher::make('Характеристика по умолчанию', 'is_default')->hint('Необходимая характеристика для работы сайта, удаление невозможно')->default(false)->disabled(true),
                        Text::make('Название','name')->required(),
                        Slug::make('SEO slug','slug')->from('name')->unique()->hint('Для разработчиков'),
                        Enum::make('Тип поля','input_type')->attach(InputTypeEnum::class),
                        Grid::make([
                            Column::make(
                                [
                                    Json::make('Варианты выбора', 'options')->onlyValue()->showWhen('input_type', InputTypeEnum::select)->creatable(true)->removable(true),
                                ],
                                colSpan: 3,
                                adaptiveColSpan: 12
                            ),
                            Column::make(
                                [
                                    Switcher::make('Можно выбрать несколько вариантов', 'is_many_checked_options')->showWhen('input_type', InputTypeEnum::select),
                                ],
                                colSpan: 3,
                                adaptiveColSpan: 12
                            ),
                            Column::make(
                                [
                                    Switcher::make('Можно вписать пользовательские значения', 'is_select_writable')->showWhen('input_type', InputTypeEnum::select),
                                ],
                                colSpan: 3,
                                adaptiveColSpan: 12
                            ),

                        ]),
                        Switcher::make('Обязательно для заполнения', 'is_required')->default(false)->hint('Без заполнения этого атрибута невозможно будет добавить или редактировать продукт'),
                        Switcher::make('Активный', 'is_active')->hint('показывать при заполнении и редактировании продуктов')->default(true),
                       /* Switcher::make('Показать в меню','is_show_menu')->default(true)->updateOnPreview()->hint(''),*/
                    ]),
//                    Tab::make('Категории', [
//                        Preview::make('Категории', 'categories', fn () => view('moonshine.components.categories-selected' , [
//                            'categoriesIdCheckedArray' => $this->getItem() ? $this->getItem()->attribute_categories()->pluck('category_id')->toArray() : [], 'defaultChecked' => $this->getItem() ? false : true])),
//                    ]),
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
            Text::make('Название','name'),
            Date::make('Дата создания','created_at'),
            Date::make('Дата обновления','updated_at'),

        ];
    }

    protected function afterCreated(mixed $item): mixed
    {
        $this->setCategories($item);
        return $item;
    }
    protected function beforeCreating(mixed $item): mixed
    {

        if ($this->getModel()::where('name', request()->name)->where('id', '!=', $item->id)->exists()) {

            throw ValidationException::withMessages([
                'name' => ['Запись с таким названием уже существует.'],
            ]);
        }
        $this->setCategories($item);
        return $item;
    }

    protected function afterUpdated(mixed $item): mixed
    {
        $this->setCategories($item);
        return $item;
    }


    protected function setCategories(Attribute $item): void
    {
        $categories = request()->input('categories');
        AttributeCategory::where('attribute_id',$item->id)->delete();
        if($categories)
        foreach ($categories as $categoryId => $checkboxSelectedOn) {
            AttributeCategory::create(
                ['attribute_id' => $item->id, 'category_id' => $categoryId],
            );
        }
    }

    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(function ($item){ return !$item->is_default; });
    }

    protected function rules(mixed $item): array
    {
        return [];
    }
}
