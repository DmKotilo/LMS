<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\Recommend;

use App\Modules\Product\Product\src\Models\RecomendedProduct;
use App\MoonShine\Resources\Product\ProductResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<RecomendedProduct>
 */
class RecommendedProductResource extends ModelResource
{
    protected string $model = RecomendedProduct::class;
    protected string $title = 'Рекомендуевые товары';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            BelongsTo::make('Страницы','page','name',RecommendedPageResource::class),
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
                BelongsTo::make('Продукт','product','name',ProductResource::class)
                    ->required()
                    ->placeholder('Начните вводить название...')
                    ->valuesQuery(fn (Builder $query) => $query->where('is_active', true)->orderBy('id')->limit(20))
                    ->asyncSearch(
                        'name',
                        searchQuery: function (Builder $query, $request, string $term, $field) {
                            $normalizedTerm = Str::lower($term);
                            $numericTerm = ctype_digit($term) ? (int) $term : null;

                            return $query
                                ->where('is_active', true)
                                ->where(function ($searchQuery) use ($normalizedTerm, $numericTerm) {
                                    $searchQuery->whereRaw('LOWER(name) LIKE ?', ["%{$normalizedTerm}%"]);

                                    if ($numericTerm !== null) {
                                        $searchQuery->orWhere('article_number', $numericTerm);
                                    }
                                })
                                ->orderBy('id')
                                ->limit(20);
                        },
                        limit: 20
                    ),
                BelongsTo::make('Страницы','page','name',RecommendedPageResource::class),
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
            BelongsTo::make('Страницы','page','name',RecommendedPageResource::class),
        ];
    }

    /**
     * @param RecomendedProduct $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'product_id' => [
                'required',
                'exists:products,id',
                function ($attribute, $value, $fail) use ($item) {
                    $pageId = $item->page_id ?? request()->input('recomended_product_page_id');

                    $exists = \App\Modules\Product\Product\src\Models\RecomendedProduct::where('recomended_product_page_id', $pageId)
                        ->where('product_id', $value)
                        ->exists();

                    if ($exists) {
                        $fail('Этот продукт уже добавлен на эту страницу.');
                    }
                },
            ],
        ];
    }
}
