<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Delivery;

use Address\Model\Address;
use App\MoonShine\Resources\Address\AddressResource;
use App\MoonShine\Resources\System\CityResource;
use City\Models\City;
use Delivery\Models\PickupLocation;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Url;

/**
 * @extends ModelResource<PickupLocation>
 */
class PickupLocationResource extends ModelResource
{
    protected string $model = PickupLocation::class;

    protected string $title = 'Пункты самовывоза';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','title')->sortable(),
            BelongsTo::make('Город', 'city_relation', 'name',  CityResource::class)->sortable(),
            Text::make('Полный адрес','full_address'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {

        $selectedCityId = $this->item?->city_id;

        $filteredCities = City::whereDoesntHave('pickupLocations');

        if ($selectedCityId) {
            $filteredCities->orWhere('id', $selectedCityId);
        }

        $options = $filteredCities
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if(isset($this->item->dadata_json)) $this->item->dadata_json = json_encode($this->item->dadata_json, JSON_UNESCAPED_UNICODE);
        return [
            Box::make([
                ID::make(),
                Text::make('Название','title')->required(),
                Flex::make([
                    Url::make('Сайт URL','site')->nullable(),
                    Phone::make('Телефон','phone')->mask('+7 999 999-99-99')->nullable()->default('+7'),
                    Text::make('Время работы: начало','working_hours_from')->setAttribute('type', 'time')->nullable(),
                    Text::make('Время работы: конец','working_hours_to')->setAttribute('type', 'time')->nullable(),
                    Number::make('Время доставки в днях','delivery_time')->nullable(),
                ]),

//                BelongsTo::make('Город (система)', 'city_relation', 'name',  CityResource::class)->required()->setId('city_relation'),

                Select::make('Город', 'city_id')
                    ->required()
                    ->setId('city_relation')
                    ->options($options),


                Text::make('Адрес Dadata', 'value_dadata')->setId('address')->sortable()->addAssets([
                    Css::make('https://cdn.jsdelivr.net/npm/@dadata/suggestions@25.4.1/dist/suggestions.min.css'),
                    Js::make('https://cdn.jsdelivr.net/npm/@dadata/suggestions@25.4.1/dist/suggestions.min.js'),
                    Js::make('/assets/js/dadata.locality.init.js'),
                ]),

                Collapse::make('Подробная информация', [
                    Flex::make([
                        Hidden::make('Регион', 'region_dadata')->setId('region_dadata'),
                        Hidden::make('Город', 'city_dadata', )->setId('city_dadata')->required(),
                        Text::make('Улица', 'street')->setId('street')->required(),
                        Text::make('Дом', 'house')->setId('house')->required(),
                        Text::make('Подъезд', 'entrance')->setId('entrance'),
                        Text::make('Этаж', 'floor')->setId('floor'),
                        Text::make('Квартира', 'apartment')->setId('apartment'),
                        Text::make('Индекс', 'postal_code')->setId('postal_code'),
                        Text::make('Широта', 'latitude')->setId('latitude')->required(),
                        Text::make('Долгота', 'longitude')->setId('longitude')->required(),
                    ]),


                ])->open(true),

                Collapse::make('JSON Dadata', [
                    Textarea::make('', 'dadata_json')->setId('dadata_json')->nullable(),
                ])->open(false)
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
        ];
    }

    /**
     * @param PickupLocation $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
