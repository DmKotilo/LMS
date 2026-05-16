<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Localization;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Localization\Models\LangStaticKey;
use Localization\Models\LangStaticKeyGroup;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<LangStaticKey>
 */
class LangStaticKeyResource extends ModelResource
{
    protected string $model = LangStaticKey::class;

    protected array $with = ['lang_static_key_group'];

    protected string $title = 'Ключи локализации';

/*    protected array $with = ['lang_static_key_group'];

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
            Text::make('Ключ', 'key'),
            BelongsTo::make('Группа', 'lang_static_key_group', 'description', resource: LangStaticKeyGroupResource::class),
            Switcher::make('По умолчанию', 'is_default')->readonly(true),
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
                Text::make('Ключ', 'key'),
                BelongsTo::make('Группа', 'lang_static_key_group', 'description', resource: LangStaticKeyGroupResource::class),
                Switcher::make('По умолчанию', 'is_default')->readonly(true),
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
            Text::make('Ключ', 'key'),
            BelongsTo::make('Группа', 'lang_static_key_group', 'description', resource: LangStaticKeyGroupResource::class),
            Switcher::make('По умолчанию', 'is_default')->readonly(true),
        ];
    }

    /**
     * @param LangStaticKey $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function queryTags(): array
    {
        $queryTags = [];
        foreach(LangStaticKeyGroup::all() as $langStaticKeyGroup) {
            $queryTags[] = QueryTag::make(
                $langStaticKeyGroup->description, // Заголовок тега
                fn(Builder $query) => $query->whereHas('lang_static_key_group', fn($q) => $q->where('title', $langStaticKeyGroup->title)) // Query builder
            );
        }
        return [
            ...$queryTags

        ];
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
