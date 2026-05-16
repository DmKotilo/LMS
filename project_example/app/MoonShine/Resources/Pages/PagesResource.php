<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Pages;

use App\Livewire\Moonshine\Components\CategoriesSelected;
use App\MoonShine\Traits\Localization\HasMoonshineTranslate;
use App\View\Moonshine\CategoriesSelectedView;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Attribute\Models\Attribute as AttributeProduct;
use Attribute\Models\AttributeCategory;
use Attribute\Models\ProductAttribute;
use Carbon\Carbon;
use Category\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
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
use Pages\Models\Page;
use Product\Models\Product;
use Recipe\Models\Recipe;

/**
 * @extends ModelResource<Attribute>
 */
class PagesResource extends ModelResource
{
    use HasMoonshineTranslate;
    protected string $model = Page::class;

    protected string $title = 'Страницы';

/*    protected bool $isAsync = false;*/

    /**
     * @return FieldContract
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','seo_title'),
            Switcher::make('Активен','is_active'),
        ];
    }

    /**
     * @return FieldContract
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

        $isEditPage = $this->getItem() != null;
        $modelClass = $this->getModel()::class;
        $model = $this->getModel();
        if ($isEditPage){
            $localizations = Localization::where('localizationable_type', $this->getModel()::class)
                ->where('localizationable_id', $this->getItem()->id)
                ->get()
                ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
        }

        $slugSourceField = 'seo_title'; // или 'name', если 'seo_title' не всегда заполнено

        return [
            Box::make([
                Tabs::make([
                    Tab::make('Основное', [
                        Text::make('seo_title','seo_title'),
                        Text::make('seo_description','seo_description'),
                        Text::make('seo_keywords', 'keywords')
                            ->tags(10),
                        Slug::make('Slug','slug')->from("seo_title"),

                    ]),
                    Tab::make('Контент страницы', [
                        TinyMce::make('Контент','content')
                            ->addOption('extended_valid_elements', 'svg[*],path[*],g[*],defs[*],use[*],symbol[*],view[*],circle[*],rect[*],line[*],polyline[*],polygon[*],ellipse[*],text[*],tspan[*]')
                            ->addOption('valid_children', '+body[svg]')
                            ->addOption('forced_root_block', false)
                            ->addOption('sandbox_iframes', false),
                        Switcher::make('Активен','is_active')->default(true),

                    ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => Page::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),
                ]),
            ])
        ];
    }

    /**
     * @return FieldContract
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название','seo_title'),
            Text::make('Описание','seo_description'),
            Slug::make('Slug','slug')->from("seo_title"),
            TinyMce::make('Контент','content'),

        ];
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


    protected function rules(mixed $item): array
    {
        return [];
    }
}
