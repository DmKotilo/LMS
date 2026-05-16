<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\Recommend;

use App\Modules\Product\Product\src\Enums\PageSlugEnum;
use App\Modules\Product\Product\src\Models\RecomendedProductPage;
use App\MoonShine\Resources\Product\ProductResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<RecomendedProductPage>
 */
class RecommendedPageResource extends ModelResource
{
    protected string $model = RecomendedProductPage::class;

    protected string $title = 'Страницы для рекомендаций продуктов';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Enum::make('slug','slug')->attach(PageSlugEnum::class),
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
                Text::make('Название','name'),
                Enum::make('slug','slug')->attach(PageSlugEnum::class),
                HasMany::make(
                    'Рекомендуемые товары',
                    'recomendedProducts',
                    'id',
                    RecommendedProductResource::class
                )
                    ->creatable()

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
            Text::make('Название','name'),
            Enum::make('slug','slug')->attach(PageSlugEnum::class),
            HasMany::make(
                'Рекомендуемые товары',
                'recomendedProducts',
                'id',
                RecommendedProductResource::class
            ),
        ];
    }

    /**
     * @param RecomendedProductPage $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
