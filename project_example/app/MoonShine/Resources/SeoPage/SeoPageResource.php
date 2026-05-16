<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SeoPage;

use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use SeoPage\Models\SeoPage;

/**
 * @extends ModelResource<SeoPage>
 */
class SeoPageResource extends ModelResource
{
    protected string $model = SeoPage::class;

    protected string $title = 'SEO-данные';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Страница','page_name'),
            Text::make('Slug','slug'),
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
                Text::make('Страница','page_name')->required(),
                Text::make('Slug','slug')->required(),
                Text::make('seo_title')->nullable(),
                Textarea::make('seo_description')->nullable(),
                Text::make('seo_keywords', 'seo_keywords')
                    ->tags(15)
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
            Text::make('Страница','page_name'),
            Text::make('Slug','slug'),
            Text::make('seo_title'),
            Textarea::make('seo_description'),
            Text::make('seo_keywords', 'seo_keywords')
                ->tags(15)
        ];
    }

    /**
     * @param SeoPage $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
