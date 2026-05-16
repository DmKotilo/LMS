<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Combination;

use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Product\ProductResource;
use Combination\Models\Drink;
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
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Drink>
 */
class DrinkResource extends ModelResource
{
    protected string $model = Drink::class;

    protected string $title = 'Напитки';

    protected function search(): array
    {
        return [
            'name',
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
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => array(
                'style' => 'width: 200px; height: 200px;'
            )),
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
                   Tab::make('Основное',[
                       Text::make('Название','name')->required(),
                       Textarea::make('Описание','description')->nullable(),
                       ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => array(
                           'style' => 'width: 200px; height: 200px;'
                       )),
                   ]) ,
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => $model::getTransaledField(),
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
            Text::make('Название','name'),
            Textarea::make('Описание','description'),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => array(
                'style' => 'width: 200px; height: 200px;'
            )),
        ];
    }

    /**
     * @param Drink $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'image' => isset($this->getItem()->image) ? ['nullable'] : ['required'],

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
}
