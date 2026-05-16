<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Pages;

use App\MoonShine\CustomFields\ImageCustom;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Pages\Models\Reward;

/**
 * @extends ModelResource<Reward>
 */
class RewardResource extends ModelResource
{
    protected string $model = Reward::class;

    protected string $title = 'Награды';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Number::make('Порядковый номер','position'),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
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
                Number::make('Порядковый номер','position')->default(0),
                ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 200px; height: 200px;'
                ])->required(),
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
            Number::make('Порядковый номер','position'),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
        ];
    }

    /**
     * @param Reward $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
