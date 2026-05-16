<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use Category\Models\MainCategory;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<MainCategory>
 */
class MainCategoryResource extends ModelResource
{
    protected string $model = MainCategory::class;

    protected string $title = 'Категории в виде фото';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Катеория','category','name',ProductCategoryResource::class),
            Text::make('Название','name'),
            Switcher::make('Активна','is_active'),
            Number::make('Поряд-ый номер','position')

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
                    Tab::make('Основное', [
                        BelongsTo::make('Катеория','category','name',ProductCategoryResource::class)->required(),
                        Text::make('Название','name')->required(),
                        Text::make('Описание','description')->nullable(),
                    ]),

                    Tab::make('Настройки', [
                        Color::make('Цвет текста','text_color')->default('#ffffff')->hint('Тёмно-синий цвет брендбука: #02283F'),
                        Text::make('Размер шрифта desk','desktop_font_size')->required()->hint('npx'),
                        Text::make('Размер шрифта mob','mobile_font_size')->required()->hint('npx'),
                        Text::make('Размер строки desk','desktop_line_height')->required()->hint('npx'),
                        Text::make('Размер строки mob','mobile_line_height')->required()->hint('npx'),
                        Number::make('Поряд-ый номер','position')->default(0),
                        Switcher::make('Активна','is_active')->default(false),
                    ]),

                    Tab::make('Фото', [
                        Image::make('Фото на компьютеры','desktop_photo')->dir('MainCategory/desktop/'),
                        Image::make('Фото на телефоны','mobile_photo')->dir('MainCategory/mobile/'),
                    ]),
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
            BelongsTo::make('Катеория','category','name',ProductCategoryResource::class),
            Text::make('Название','name'),
            Text::make('Описание','description'),
            Color::make('Цвет текста','text_color'),
            Text::make('Размер шрифта desk','desktop_font_size')->required(),
            Text::make('Размер шрифта mob','mobile_font_size')->required(),
            Text::make('Размер строки desk','desktop_line_height')->required(),
            Text::make('Размер строки mob','mobile_line_height')->required(),
            Image::make('Фото на компьютеры','desktop_photo'),
            Image::make('Фото на телефоны','mobile_photo'),
            Switcher::make('Активна','is_active'),
            Number::make('Поряд-ый номер','position'),

        ];
    }

    /**
     * @param MainCategory $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'desktop_photo' => isset($this->getItem()->desktop_photo) ? ['nullable'] : ['required'],
            'mobile_photo' => isset($this->getItem()->mobile_photo) ? ['nullable'] : ['required'],

        ];
    }
}
