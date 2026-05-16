<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Delivery;

use App\MoonShine\Resources\System\CityResource;
use Delivery\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<DeliveryZone>
 */
class DeliveryZoneResource extends ModelResource
{
    protected string $model = DeliveryZone::class;

    protected string $title = 'Зоны доставки';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            BelongsTo::make('Город','city','name',CityResource::class),
            Text::make('Название','name'),
            Text::make('Мин сумма заказа','min_order_sum'),
            ID::make()->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Tabs::make([
                   Tab::make('Основное',[
                       BelongsTo::make('Город','city','name',CityResource::class),
                       Text::make('Название','name'),
                       Number::make('Примерное время доставки (в днях)','delivery_time')->nullable(),
                       Flex::make([
                           Number::make('Мин сумма заказа','min_order_sum')->step(0.100)->default(0),
                           Number::make('Цена доставки','delivery_price')->step(0.100)->default(0),
                       ]),

                       Json::make('Интервалы доставки', 'time_intervals')
                           ->fields([
                               Text::make('С','from'),
                               Text::make('До','to'),
                           ])
                   ]),
                    Tab::make('Описание',[
                        Textarea::make('Описание','description')->nullable(),
                        ])
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

            BelongsTo::make('Город','city','name',CityResource::class),
            Text::make('Название','name'),
            Number::make('Мин сумма заказа','min_order_sum')->step(0.100)->default(0),
            Text::make('Примерное время доставки','delivery_time'),
            Number::make('Цена доставки','delivery_price')->step(0.100)->default(0),
            Json::make('Интервалы доставки', 'time_intervals')
                ->fields([
                    Text::make('С','from'),
                    Text::make('До','to'),
                ]),
            Textarea::make('Описание','description')->nullable(),
        ];
    }

    /**
     * @param DeliveryZone $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
