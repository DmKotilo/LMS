<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\System;

use Faq\Models\Faq;
use Illuminate\Database\Eloquent\Model;

use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Product\Models\Product;

/**
 * @extends ModelResource<Faq>
 */
class FaqResource extends ModelResource
{
    protected string $model = Faq::class;
    protected bool  $isAsync = false;
    protected string $title = 'FAQ';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Раздел','section','name',FaqSectionResource::class),
            Text::make('Название','name'),

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
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       ID::make(),
                       BelongsTo::make('Раздел','section','name',FaqSectionResource::class)->required(),
                       Text::make('Название','name')->required(),
                       Number::make('Порядковый номер','position'),
                       Flex::make([
                           Text::make('Текст кнопки','button_text'),
                           Text::make('Ссылка кнопки','link_button'),
                       ]),

                   ]) ,
                    Tab::make('Контент', [
                        TinyMce::make('Контент','description')
                            ->addOption('extended_valid_elements', 'iframe[src|width|height|frameborder|allow|allowfullscreen|title|class]')
                            ->addOption('sandbox_iframes', false),
                        ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => Faq::getTransaledField(),
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
            BelongsTo::make('Раздел','section','name',FaqSectionResource::class),
            Text::make('Название','name'),
            Number::make('Порядковый номер','position'),
            TinyMce::make('Контент','description'),
            Text::make('Текст кнопки','button_text'),
            Text::make('Ссылка кнопки','link_button'),
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
     * @param Faq $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
