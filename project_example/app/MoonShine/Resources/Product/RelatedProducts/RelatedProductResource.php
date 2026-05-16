<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\RelatedProducts;

use App\MoonShine\Resources\Product\ProductResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use Product\Models\RelatedProduct;

/**
 * @extends ModelResource<RelatedProduct>
 */
class RelatedProductResource extends ModelResource
{
    protected string $model = RelatedProduct::class;

    protected string $title = 'С этим товаром покупают';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            BelongsTo::make('Связанный продукт','related','name',ProductResource::class),
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
                BelongsTo::make('Продукт','product','name',ProductResource::class),
                BelongsTo::make('Связанный продукт','related','name',ProductResource::class),
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
            BelongsTo::make('Связанный продукт','related','name',ProductResource::class),
        ];
    }

    /**
     * @param RelatedProduct $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
