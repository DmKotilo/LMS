<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\System;

use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use App\MoonShine\Resources\Promotion\PromotionResource;
use City\Models\City;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Product\Models\Product;

/**
 * @extends ModelResource<City>
 */
class CityResource extends ModelResource
{
    protected string $model = City::class;

    protected string $title = 'Города';

    /**
     * @return FieldContract
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Number::make('Позиция', 'position')->nullable()->sortable(),
            Text::make('uuid_1c'),
            Text::make('Название','name'),
            Text::make('Широта','latitude'),
            Text::make('Долгота','longitude'),
            Number::make('Бонус за самовывоз','pickup_bonus_points'),
            Switcher::make('Активность','is_active'),
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
            Box::make([
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       ID::make(),
                       Number::make('Позиция', 'position')
                           ->nullable()
                           ->min(0)
                           ->step(1),
                       Text::make('uuid_1c')->nullable(),
                       Text::make('Название','name')->required(),
                       Text::make('Широта','latitude'),
                       Text::make('Долгота','longitude'),
                       Number::make('Бонус за самовывоз','pickup_bonus_points')
                           ->min(0)
                           ->max(10000)
                           ->step(1)
                           ->default(0)
                           ->hint('Максимальное количество баллов: 10000'),
                       Switcher::make('Активность','is_active')->default(true),
                   ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => City::getTransaledField(),
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
            Number::make('Позиция', 'position'),
            Text::make('uuid_1c'),
            Text::make('Название','name'),
            Text::make('Широта','latitude'),
            Text::make('Долгота','longitude'),
            Number::make('Бонус за самовывоз','pickup_bonus_points'),
            Switcher::make('Активность','is_active'),
            HasMany::make('Зоны доставки','deliveryZones','name',DeliveryZoneResource::class),
            BelongsToMany::make('Акции','promotions','name',PromotionResource::class)
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
     * @param City $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'pickup_bonus_points' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function beforeSaving(mixed $item): mixed
    {
        if (isset($item->pickup_bonus_points) && $item->pickup_bonus_points > 10000) {
            throw new \Exception('Количество баллов за самовывоз не может превышать 10000');
        }

        return $item;
    }
}
