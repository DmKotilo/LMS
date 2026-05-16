<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Gradebook;

use Gradebook\Models\Gradebook;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use User\Enums\UserRole;
use User\Models\User;

/**
 * @extends ModelResource<Gradebook>
 */
class GradebookResource extends ModelResource
{
    protected string $model = Gradebook::class;

    protected string $title = 'Ведомости';

    protected array $with = ['teacher'];

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title'),
            Text::make('Дисциплина', 'discipline'),
            Text::make('Группа', 'group_name'),
            Text::make('Семестр', 'semester'),
            BelongsTo::make('Преподаватель', 'teacher', formatted: static fn (?User $user) => $user?->fullName() ?? '—'),
            Date::make('Загружена', 'created_at')->format('d.m.Y H:i'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('Название', 'title')->required(),
                Text::make('Дисциплина', 'discipline'),
                Text::make('Группа', 'group_name'),
                Text::make('Семестр', 'semester'),
                BelongsTo::make('Преподаватель', 'teacher')
                    ->valuesQuery(static fn ($q) => $q->where('role', UserRole::Teacher->value))
                    ->searchable(),
                Text::make('Исходный файл', 'original_filename'),
            ]),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            HasMany::make('Строки', 'rows', resource: GradebookRowResource::class),
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'discipline' => ['nullable', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:50'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
