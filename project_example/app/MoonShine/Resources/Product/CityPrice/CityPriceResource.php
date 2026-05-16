<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\CityPrice;

use App\Modules\City\src\Models\CityProductPrice;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\System\CityResource;
use Faker\Core\Number;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<CityProductPrice>
 */
class CityPriceResource extends ModelResource
{
    protected string $model = CityProductPrice::class;

    protected string $title = 'Цена продуктов по городам';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Город','city','name',CityResource::class),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            \MoonShine\UI\Fields\Number::make('Цена','price'),
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
                BelongsTo::make('Город','city','name',CityResource::class),
                BelongsTo::make('Продукт','product','name',ProductResource::class),
                \MoonShine\UI\Fields\Number::make('Цена','price'),
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
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            \MoonShine\UI\Fields\Number::make('Цена','price'),
        ];
    }

    /**
     * @param CityProductPrice $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
