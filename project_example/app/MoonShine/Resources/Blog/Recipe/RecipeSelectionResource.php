<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Recipe;

use Illuminate\Database\Eloquent\Model;

use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Recipe\Models\Recipe;
use Recipe\Models\RecipeSelection;

/**
 * @extends ModelResource<RecipeSelection>
 */
class RecipeSelectionResource extends ModelResource
{
    protected string $model = RecipeSelection::class;

    protected string $title = 'Категории рецептов';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','title'),
            Switcher::make('Активность','is_displayed'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
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
            Box::make([
                ID::make(),
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       Text::make('Название','title')->required(),
                       Number::make('Порядковый номер','order_number')->nullable(),
                       Switcher::make('Активность','is_displayed')->nullable(),
                       BelongsToMany::make('Рецепты','recipes','title',RecipeResource::class)->selectMode()->searchable(),
                   ]),
                    Tab::make('Переводы', [
                        Preview::make('Preview', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => RecipeSelection::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),
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
            Text::make('Название','title'),
            Number::make('Порядковый номер','order_number'),
            Switcher::make('Активность','is_displayed'),
            BelongsToMany::make('Рецепты','recipes','title',RecipeResource::class),
        ];
    }

    /**
     * @param RecipeSelection $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
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
