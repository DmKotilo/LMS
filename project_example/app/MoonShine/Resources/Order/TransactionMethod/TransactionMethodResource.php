<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Order\TransactionMethod;

use App\MoonShine\CustomFields\ImageCustom;
use Article\Models\Article;
use Illuminate\Database\Eloquent\Model;

use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Resources\ModelResource;
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
use Order\Models\TransactionMethod;

/**
 * @extends ModelResource<TransactionMethod>
 */
class TransactionMethodResource extends ModelResource
{
    protected string $model = TransactionMethod::class;

    protected string $title = 'Способы оплаты';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Switcher::make('Активность','is_active'),
            ImageCustom::make('Иконка','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 50px; height:50px;'
            ]),
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
                       Text::make('Название','name'),
                       Textarea::make('Описание','description'),
                       Switcher::make('Активность','is_active'),
                       ImageCustom::make('Иконка','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                           'style' => 'width: 50px; height: 50px;'
                       ]),
                   ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => TransactionMethod::getTransaledField(),
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
            Switcher::make('Активность','is_active'),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 50px; height: 50px;'
            ]),
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
    /**
     * @param TransactionMethod $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
