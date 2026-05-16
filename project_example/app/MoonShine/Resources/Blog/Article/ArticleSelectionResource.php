<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Article;

use App\MoonShine\CustomFields\ImageCustom;
use Article\Models\ArticleSelection;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<ArticleSelection>
 */
class ArticleSelectionResource extends ModelResource
{
    protected string $model = ArticleSelection::class;

    protected string $title = 'Категории новостей';

    /**
     * @return FieldContract
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Number::make('Количество просмотров','views_count'),
            Text::make('Заголовок', 'title'),
            Number::make('Порядковый номер','order_number'),
            Switcher::make('Активность','is_displayed'),
            Switcher::make('Популярная','is_popular'),
            Switcher::make('Основная','is_main'),
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

                Tabs::make([
                    Tabs\Tab::make('Основное',[
                        Text::make('Заголовок', 'title')->required(),
                        Textarea::make('Краткое описание','description')->nullable(),
                        Number::make('Порядковый номер','order_number')->nullable(),
                        Switcher::make('Активность','is_displayed')->default(false),
                        Number::make('Количество просмотров','views_count')->default(0),
                        Switcher::make('Популярная Категория','is_popular')->default(false),
                        Switcher::make('Основная','is_main')->default(false)->hint('Только 1 категория может быть основной'),


                    ]),
                    Tabs\Tab::make('Изображение',[
                        ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ]),
                    ]),

                ]),

                BelongsToMany::make('Статьи','articles','title',ArticleResource::class)->selectMode()->searchable(),
            ])
        ];
    }

    /**
     * @return FieldContract
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Text::make('Заголовок', 'title'),
            Textarea::make('Краткое описание','description'),
            Number::make('Порядковый номер','order_number'),
            Number::make('Количество просмотров','views_count'),
            Switcher::make('Активность','is_displayed'),
            Switcher::make('Популярная категория','is_popular'),
            Switcher::make('Основная','is_main')->default(false)->hint('Только 1 категория может быть основной'),
            BelongsToMany::make('Статьи','articles','title',ArticleResource::class)
        ];
    }

    /**
     * @param ArticleSelection $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'is_main' => [
                'boolean',
                function ($attribute, $value, $fail) use ($item) {
                    if ($value) {
                        $exists = ArticleSelection::query()
                            ->where('is_main', true)
                            ->when($item->exists, fn($q) => $q->where('id', '!=', $item->id))
                            ->exists();

                        if ($exists) {
                            $fail('Только одна подборка может быть основной.');
                        }
                    }
                },
            ],
        ];
    }
}
