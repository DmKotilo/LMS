<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\System;

use Authorization\Models\SocialAuthSetting;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Switcher;

/**
 * @extends ModelResource<SocialAuthSetting>
 */
class SocialAuthSettingResource extends ModelResource
{
    protected string $model = SocialAuthSetting::class;

    protected string $title = 'Соцсети для входа';

    protected bool $inlineEdit = true;

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only(Action::VIEW, Action::UPDATE);
    }

    public function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Соцсеть', 'name')->readonly(),
            Switcher::make('Доступна', 'is_available'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Соцсеть', 'name')->readonly(),
            Switcher::make('Доступна', 'is_available'),
        ];
    }

    public function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function resolveQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::resolveQuery()->orderBy('name');
    }
}
