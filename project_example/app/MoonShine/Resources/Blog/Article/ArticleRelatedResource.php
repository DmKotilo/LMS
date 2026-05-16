<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Article;

use App\Modules\Blog\Article\src\Models\ArticleRelated;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<ArticleRelated>
 */
class ArticleRelatedResource extends ModelResource
{
    protected string $model = ArticleRelated::class;

    protected string $title = 'Похожие статьи';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            BelongsTo::make('Статья','article','title',ArticleResource::class),
            BelongsTo::make('Похожая','related','title',ArticleResource::class),
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
                BelongsTo::make('Статья','article','title',ArticleResource::class)->required(),
                BelongsTo::make('Похожая','related','title',ArticleResource::class)->required(),
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
            BelongsTo::make('Статья','article','title',ArticleResource::class),
            BelongsTo::make('Похожая','related','title',ArticleResource::class),
        ];
    }

    /**
     * @param ArticleRelated $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
