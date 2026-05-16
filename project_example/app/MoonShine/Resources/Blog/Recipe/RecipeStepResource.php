<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Recipe;

use App\MoonShine\CustomFields\ImageCustom;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Recipe\Models\RecipeStep;

/**
 * @extends ModelResource<RecipeStep>
 */
class RecipeStepResource extends ModelResource
{
    protected string $model = RecipeStep::class;

    protected string $title = 'Шаг';

    public function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title'),
            Text::make('Описание', 'description'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            // ID::make()->sortable(), // Убираем, если не нужно редактировать
            Text::make('Название', 'title')->required(),
            Textarea::make('Описание', 'description'),
//            Number::make('Порядок', 'order_number'),
            // Поле для загрузки изображений шага
            ImageCustom::make('Изображение шага', 'images')
                ->multiple()
                ->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 200px; height: 200px;'
                ]),
            // Важно: добавляем поле recipe_id как скрытое или связанное
            BelongsTo::make('Рецепт', 'recipe', 'title', resource: \App\MoonShine\Resources\Blog\Recipe\RecipeResource::class)
                ->required()
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title'),
            Textarea::make('Описание', 'description'),
            Number::make('Порядок', 'order_number'),
            ImageCustom::make('Изображение шага', 'images')
                ->multiple()
                ->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 200px; height: 200px;'
                ])
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'recipe_id' => ['required', 'exists:recipes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order_number' => ['nullable', 'integer'],
        ];
    }
}
