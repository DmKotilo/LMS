<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Localization;

use Illuminate\Database\Eloquent\Model;

use Localization\Models\LangStaticKeyGroup;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<LangStaticKeyGroup>
 */
class LangStaticKeyGroupResource extends ModelResource
{
    protected string $model = LangStaticKeyGroup::class;

    protected string $title = 'Группы ключей локализации';

/*    protected array $with = ['lang_static_keys'];*/

    protected bool $isAsync = true;
    protected bool $createInModal = true;
    protected bool $detailInModal = true;
    protected bool $editInModal = true;

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Описание', 'description'),
            Text::make('Ключ группы', 'title'),
            Switcher::make('По умолчанию', 'is_default')->readonly(true),
            HasMany::make('Ключи', 'lang_static_keys', resource: LangStaticKeyResource::class)
                ->relatedLink()
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Text::make('Описание', 'description'),
                Text::make('Ключ группы', 'title'),
                Switcher::make('По умолчанию', 'is_default')->readonly(true),
                HasMany::make('Ключи', 'lang_static_keys', resource: LangStaticKeyResource::class)
                    ->relatedLink()
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Описание', 'description'),
            Text::make('Ключ группы', 'title'),
            Switcher::make('По умолчанию', 'is_default')->readonly(true),
            HasMany::make('Ключи', 'lang_static_keys', resource: LangStaticKeyResource::class)
                ->relatedLink()
        ];
    }

    /**
     * @param LangStaticKeyGroup $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(function ($item){ return !$item->is_default; });
    }

    protected function modifyEditButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(function ($item){ return !$item->is_default; });
    }
}
