<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\Manufacturer;

use App\MoonShine\CustomFields\ImageCustom;
use Illuminate\Database\Eloquent\Model;
use Manufacturer\Models\Manufacturer;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Manufacturer>
 */
class ManufacturerResource extends ModelResource
{
    protected string $model = Manufacturer::class;

    protected string $title = 'Производители';

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
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       Text::make('Название','name'),
                       Collapse::make('Контент',[
                           TinyMce::make('Контент','content')->nullable(),
                           ]),
                   ]) ,
                    Tabs\Tab::make('Изображение',[
                        ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ]),
                   ]) ,
                ]),



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
            TinyMce::make('Контент','content')->nullable(),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
        ];
    }

    /**
     * @param Manufacturer $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
