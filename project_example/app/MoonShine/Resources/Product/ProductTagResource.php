<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use Tag\Models\Tag;

/**
 * @extends ModelResource<Tag>
 */
class ProductTagResource extends ModelResource
{
    protected string $model = Tag::class;

    protected string $title = 'Тэги продуктов';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
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
                Text::make('Название','name')->required(),
                Slug::make('SEO slug','slug')->from('name')->unique(),
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
            Date::make('Дата создания','created_at'),
            Date::make('Дата обновления','updated_at'),
        ];
    }

    /**
     * @param Tag $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
