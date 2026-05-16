<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Recipe;

use App\Modules\Blog\Recipe\src\Enums\RecipeVariantEnum;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use Recipe\Models\RecipeVariant;

/**
 * @extends ModelResource<RecipeVariant>
 */
class RecipeVariantResource extends ModelResource
{
    protected string $model = RecipeVariant::class;

    protected string $title = 'Вариант рецепта';

    /**
     * @return FieldContract
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Enum::make('Наименование', 'name')->attach(RecipeVariantEnum::class),
        ];
    }

    /**
     * @return FieldContract
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Enum::make('Наименование', 'name')->attach(RecipeVariantEnum::class),
            ])
        ];
    }

    /**
     * @return FieldContract
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Enum::make('Наименование', 'name')->attach(RecipeVariantEnum::class),
        ];
    }

    /**
     * @param RecipeVariant $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
