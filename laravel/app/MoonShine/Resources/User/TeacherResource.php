<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use User\Enums\UserRole;
use User\Models\User;

/**
 * @extends ModelResource<User>
 */
#[SkipMenu]
class TeacherResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Преподаватели';

    protected string $column = 'last_name';

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->where('role', UserRole::Teacher->value);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Фамилия', 'last_name'),
            Text::make('Имя', 'first_name'),
            Text::make('Второе имя', 'second_name'),
            Email::make('E-mail', 'email'),
        ];
    }

    protected function formFields(): iterable
    {
        return $this->indexFields();
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }
}
