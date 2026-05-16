<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\ComboProduct;

use App\MoonShine\Resources\Product\ProductResource;
use Combo\Models\ComboProduct;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;

/**
 * @extends ModelResource<ComboProduct>
 */
class ComboProductResource extends ModelResource
{
    protected string $model = ComboProduct::class;

    protected string $title = 'Товары в комбонаборе';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            \MoonShine\UI\Fields\Number::make('Цена','price'),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
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
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            \MoonShine\UI\Fields\Number::make('Цена','price'),
        ];
    }

    /**
     * @param ComboProduct $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}

