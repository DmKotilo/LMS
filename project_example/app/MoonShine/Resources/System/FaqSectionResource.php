<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\System;

use App\Modules\Faq\src\Enums\FaqSectionPageSlugEnum;
use App\Modules\Faq\src\Models\FaqSection;
use Exception;
use Illuminate\Database\Eloquent\Model;

use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Product\Models\Product;

/**
 * @extends ModelResource<FaqSection>
 */
class FaqSectionResource extends ModelResource
{
    protected string $model = FaqSection::class;

    protected string $title = 'Разделы FAQ';
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name')
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
                       Text::make('Название','name')->required()
                   ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => FaqSection::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),
                ]),

            ]),
            HasMany::make('Содержание','faqs','name',FaqResource::class)->creatable()
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название','name')
        ];
    }

    /**
     * @param FaqSection $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function beforeUpdating(mixed $item): mixed
    {
        $systemTypes = [FaqSectionPageSlugEnum::Promo->value, FaqSectionPageSlugEnum::Combo->value];

        if (in_array($item->getOriginal('page_slug'), $systemTypes)) {
            if ($item->isDirty(['name', 'page_slug'])) {
                $changedField = $item->isDirty('page_slug') ? 'типа' : 'названия';
                throw new Exception("Системный раздел ({$item->page_slug?->value}) не поддерживает изменения {$changedField}.");
            }
        }

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

    protected function beforeDeleting(mixed $item): mixed
    {
        $systemTypes = [FaqSectionPageSlugEnum::Promo->value, FaqSectionPageSlugEnum::Combo->value];

        if (in_array($item->page_slug?->value, $systemTypes)) {
            throw new Exception("Системный раздел типа '{$item->page_slug?->value}' нельзя удалить.");
        }

        return $item;
    }
    protected function rules(mixed $item): array
    {
        return [];
    }
}
