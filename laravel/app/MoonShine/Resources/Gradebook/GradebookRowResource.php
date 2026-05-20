<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Gradebook;

use App\MoonShine\Resources\User\UserResource;
use Gradebook\Models\GradebookRow;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use User\Enums\UserRole;
use User\Models\User;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<GradebookRow>
 */
class GradebookRowResource extends ModelResource
{
    protected string $model = GradebookRow::class;

    protected string $title = 'Строки ведомости';

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Студент', 'student_name'),
            Text::make('Группа', 'group_name'),
            Number::make('Модуль 1', 'module1_score'),
            Number::make('Модуль 2', 'module2_score'),
            Number::make('Экзамен', 'exam_score'),
            Number::make('Итого', 'total_score'),
            Text::make('Оценка', 'final_grade'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                BelongsTo::make('Ведомость', 'gradebook', resource: GradebookResource::class)->required(),
                BelongsTo::make(
                    'Студент (аккаунт)',
                    'student',
                    formatted: static fn (?User $user) => $user?->fullName() ?? '—',
                    resource: UserResource::class,
                )
                    ->nullable()
                    ->searchable()
                    ->valuesQuery(static fn ($q) => $q->where('role', UserRole::Student->value)),
                Text::make('ФИО в ведомости', 'student_name')->required(),
                Text::make('Группа', 'group_name'),
                Text::make('Семестр', 'semester'),
                Number::make('Модуль 1', 'module1_score'),
                Number::make('Модуль 2', 'module2_score'),
                Number::make('Экзамен', 'exam_score'),
                Number::make('Итого', 'total_score'),
                Text::make('Оценка', 'final_grade'),
            ]),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function rules(mixed $item): array
    {
        return [
            'gradebook_id' => ['required', 'exists:gradebooks,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'module1_score' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'module2_score' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'exam_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'total_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
