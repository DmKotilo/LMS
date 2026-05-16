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
use Pages\Models\Team;

/**
 * @extends ModelResource<Team>
 */
class TeamResource extends ModelResource
{
    protected string $model = Team::class;

    protected string $title = 'Команда';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('ФИО','full_name'),
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
                Text::make('ФИО','full_name')->required(),
                Text::make('Описание','description')->required(),
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
            Text::make('ФИО','full_name'),
            Text::make('Описание','description'),
            Number::make('Порядковый номер','position'),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
        ];
    }

    /**
     * @param Team $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
