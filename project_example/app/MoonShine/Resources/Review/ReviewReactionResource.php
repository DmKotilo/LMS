<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Review;

use App\Modules\Review\src\Enums\ReviewReactionTypeEnum;
use App\Modules\Review\src\Models\ReviewReaction;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<ReviewReaction>
 */
class ReviewReactionResource extends ModelResource
{
    protected string $model = ReviewReaction::class;

    protected string $title = 'Реакции на отзывы';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Пользователь','user','name',UserResource::class),
            BelongsTo::make('Отзыв','review','id',ReviewResource::class),
            Enum::make('Тип','type')->attach(ReviewReactionTypeEnum::class),
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
                BelongsTo::make('Пользователь','user','name',UserResource::class),
                BelongsTo::make('Отзыв','review','id',ReviewResource::class),
                Enum::make('Тип','type')->attach(ReviewReactionTypeEnum::class),
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
            BelongsTo::make('Пользователь','user','name',UserResource::class),
            BelongsTo::make('Отзыв','review','id',ReviewResource::class),
            Enum::make('Тип','type')->attach(ReviewReactionTypeEnum::class),
        ];
    }

    /**
     * @param ReviewReaction $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
