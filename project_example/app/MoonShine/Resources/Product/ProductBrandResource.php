<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use Brand\Models\Brand;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Brand>
 */
class ProductBrandResource extends ModelResource
{
    protected string $model = Brand::class;

    protected string $title = 'Бренды продуктов (из 1С)';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Text::make('Название из 1С','name_1с'),
            Text::make('uuid_1c','uuid_1c'),
            Switcher::make('Активен в 1С','is_active')->required()->readonly(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $isEditPage = $this->getItem() != null;
        return [
            Box::make([
                ID::make(),
                Tabs::make([
                    Tabs\Tab::make('Основное',[
                        ID::make(),
//                        Text::make('Название','name')->required(),
                        Text::make('Название','name')->required()->canSee(fn() => $isEditPage),
                        Text::make('Название из 1С','name_1c')->readonly(),
                        Switcher::make('Активен в 1С','is_active')->required()->disabled(),
                        Flex::make([
                            Slug::make('SEO slug','slug')->from('name')->unique(),
                            Text::make('uuid_1c','uuid_1c')->nullable(),
                        ]),
                    ]),
                    Tabs\Tab::make('Описание',[
                        TinyMce::make('Описание','description')->nullable(),
                        Text::make('Объяснение','explanation')->nullable()->readonly($isEditPage),
                    ]),
                ])
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
            Text::make('uuid_1c','uuid_1c'),
            BelongsTo::make('Родитель','parent','id',ProductBrandResource::class)->nullable(),
            Text::make('Описание','description')->nullable(),
            Text::make('Объяснение','explanation'),
            Date::make('Дата создания','created_at'),
            Date::make('Дата обновления','updated_at'),
        ];
    }

    protected function modifyCreateButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn() => false);
    }
//    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
//    {
//        return $button->canSee(fn() => false);
//    }


    /**
     * @param Brand $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
