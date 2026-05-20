<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Gradebook;

use App\MoonShine\Resources\User\TeacherResource;
use Gradebook\Models\Gradebook;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
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
            Text::make('Направление', 'direction_code'),
            Text::make('Группа', 'group_name'),
            Text::make('Семестр', 'semester'),
            Text::make('Учебный год', 'academic_year'),
            BelongsTo::make(
                'Преподаватель',
                'teacher',
                formatted: static fn (?User $user) => $user?->fullName() ?? '—',
                resource: TeacherResource::class,
            ),
            Date::make('Загружена', 'created_at')->format('d.m.Y H:i'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('Название', 'title')->required(),
                Text::make('Дисциплина', 'discipline'),
                Text::make('Направление', 'direction_code'),
                Text::make('Группа', 'group_name'),
                Text::make('Семестр', 'semester'),
                Text::make('Учебный год', 'academic_year'),
                BelongsTo::make('Преподаватель', 'teacher', resource: TeacherResource::class)
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
            'direction_code' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
