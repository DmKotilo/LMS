<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Test;


use App\Models\TestLocalization;
use App\MoonShine\Pages\Localization\LocalizationDetailPage;
use App\MoonShine\Pages\Localization\LocalizationFormPage;
use App\MoonShine\Pages\Localization\LocalizationIndexPage;
use App\MoonShine\Traits\Localization\HasMoonshineTranslate;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use MoonShine\Support\Enums\PageType;

/**
 * @extends ModelResource<TestLocalization>
 */
class TestLocalizationResource extends ModelResource
{

    use HasMoonshineTranslate;
    protected string $model = TestLocalization::class;
    protected string $title = 'тестовый ресурс';

    /**
     * @return list<FieldContract>
     */

//    protected function modifyCreateButton(ActionButtonContract $button): ActionButtonContract
//    {
//        return $button->setUrl('/admin/resource/$this/localization-create-page');
//    }

    protected function pages(): array
    {
        return [
            LocalizationIndexPage::class,
            LocalizationDetailPage::class,
            LocalizationFormPage::class,
        ];
    }



    protected function indexFields(): iterable
    {
        return [
            ID::make(),
            Text::make('title'),
        ];
    }
    public function getItemID(): int|string|null
    {
        return TestLocalization::query()->first()->id ?? null;
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('title'),
                $this->getTranslateRelation(TestLocalization::getTransaledField()),
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
            Text::make('title'),

        ];
    }

    /**
     * @param TestLocalization $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */

    protected function rules(mixed $item): array
    {
        return [];
    }
}
