<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use App\MoonShine\CustomFields\ImageCustom;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use Product\Models\CuttingProduct;

/**
 * @extends ModelResource<CuttingProduct>
 */
class CuttingProductResource extends ModelResource
{
    protected string $model = CuttingProduct::class;

    protected string $title = 'Разделка продукта';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
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
                BelongsTo::make('Продукт','product','name',ProductResource::class)->required(),
                ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 200px; height: 200px;'
                ]),
                Text::make('Текст кнопки','button_text')->unescape(),
                Text::make('Ссылка кнопки','button_link')->unescape(),
                Text::make('Ссылка на видео','video_link')->unescape(),
                TinyMce::make('Контент','content'),
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
            ImageCustom::make('Изображения','image')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            BelongsTo::make('Продукт','product','name',ProductResource::class),
            Text::make('Текст кнопки','button_text')->unescape(),
            Text::make('Ссылка кнопки','button_link')->unescape(),
            Text::make('Ссылка на видео','video_link')->unescape(),
            TinyMce::make('Контент','content'),
        ];
    }

    /**
     * @param CuttingProduct $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
        ];
    }
}
