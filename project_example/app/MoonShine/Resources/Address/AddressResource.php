<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Address;

use Address\Model\Address;
use Illuminate\Database\Eloquent\Model;

use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Textarea;


class AddressResource extends ModelResource
{
    protected string $model = Address::class;

    protected string $title = 'Адреса';

    public string $column = 'value';

    protected function indexFields(): iterable{
        return [
            ID::make()->sortable(),
            Text::make('Полный адрес', 'final_address')->required()->sortable(),
        ];
    }
    protected function detailFields(): iterable{
        return [

        ];
    }
    protected function formFields(): iterable{
        if(isset($this->item->dadata_json)) $this->item->dadata_json = json_encode($this->item->dadata_json, JSON_UNESCAPED_UNICODE);
        return [
            ID::make(),
            Text::make('Адрес Dadata', 'value')->setId('address')->sortable()->addAssets([
                Css::make('https://cdn.jsdelivr.net/npm/@dadata/suggestions@25.4.1/dist/suggestions.min.css'),
                Js::make('https://cdn.jsdelivr.net/npm/@dadata/suggestions@25.4.1/dist/suggestions.min.js'),
                Js::make('/assets/js/dadata.locality.init.js'),
                /*  Js::make('https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=bcb6f74c-ae25-418e-8698-86382852940b&suggest_apikey=3b2ea4af-be6a-4fab-8cca-367200297e25'),
                    Js::make('https://api-maps.yandex.ru/2.1/?lang=ru_RU&load=SuggestView&onload=onLoad')
                    Js::make('/assets/js/yandex.locality.init.js'),*/
            ]),
            Text::make('Полный адрес', 'final_address')->setId('final_address')->readonly()->sortable(),

            BelongsTo::make('Город', 'city', 'name')->required()->setId('city'),
            Collapse::make('Подробная информация', [
                Flex::make([
                    Text::make('Регион', 'region')->setId('region'),
                    BelongsTo::make('Город', 'city', 'name')->setId('city')->disabled(),
                    Text::make('Улица', 'street')->setId('street'),
                    Text::make('Дом', 'house')->setId('house'),
                    Text::make('Подъезд', 'entrance')->setId('entrance'),
                    Text::make('Этаж', 'floor')->setId('floor'),
                    Text::make('Индекс', 'postal_code')->setId('postal_code'),
                    Text::make('Квартира', 'apartment')->setId('apartment'),
                    Checkbox::make('Пропуск для авто', 'car_pass'),
                    Checkbox::make('Основной', 'is_primary'),
                ]),

/*             BelongsTo::make('Способ связи', 'contactMethod'),*/

            ])->open(true),

            Text::make('Доп. Информация (пользовательская, комментарий, уточнение)', 'added')->nullable(),

            Collapse::make('JSON Dadata', [
                Textarea::make('', 'dadata_json')->setId('dadata_json')->nullable(),
            ])->open(false),

        ];
    }

/*'city_id' => $addr['city_id'] ?? null,*/

    protected function beforeUpdating(mixed $item): mixed
    {
        return $item;
    }

    public function rules( $item): array
    {
        return [];
    }
}
