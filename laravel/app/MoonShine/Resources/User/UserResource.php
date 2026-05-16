<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use MoonShine\Laravel\Fields\Relationships\HasOne;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Password as PasswordField;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use User\Enums\UserRole;
use User\Models\User;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Пользователи';

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Enum::make('Роль', 'role')->attach(UserRole::class),
            Text::make('Фамилия', 'last_name'),
            Text::make('Имя', 'first_name'),
            Text::make('Второе имя', 'second_name'),
            Email::make('E-mail', 'email'),
            Text::make('Телефон', 'phone'),
            Switcher::make('Активен', 'is_active'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Enum::make('Роль', 'role')->attach(UserRole::class)->required(),
                Text::make('Фамилия', 'last_name')->required(),
                Text::make('Имя', 'first_name')->required(),
                Text::make('Второе имя', 'second_name'),
                Email::make('E-mail', 'email')->required(),
                Text::make('Телефон', 'phone'),
                PasswordField::make('Пароль', 'password')
                    ->eye()
                    ->customAttributes(['autocomplete' => 'new-password']),
                Switcher::make('Активен', 'is_active')->default(true),
                HasOne::make('Профиль студента', 'studentProfile', resource: StudentProfileResource::class),
            ]),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function rules(mixed $item): array
    {
        $userId = $item?->getKey();

        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'second_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => [$userId ? 'nullable' : 'required', 'string', Password::min(8)],
            'is_active' => ['boolean'],
        ];
    }
}
