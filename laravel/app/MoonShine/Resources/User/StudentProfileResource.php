<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use Illuminate\Validation\Rule;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use User\Enums\UserRole;
use User\Models\StudentGroup;
use User\Models\StudentProfile;
use User\Models\User;

/**
 * @extends ModelResource<StudentProfile>
 */
class StudentProfileResource extends ModelResource
{
    protected string $model = StudentProfile::class;

    protected string $title = 'Профили студентов';

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: static fn (?User $user) => $user?->fullName() ?? '—',
                resource: UserResource::class,
            ),
            BelongsTo::make(
                'Группа',
                'group',
                formatted: static fn (?StudentGroup $group) => $group?->name ?? '—',
                resource: StudentGroupResource::class,
            ),
            Text::make('№ студ. билета', 'student_id_number'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                BelongsTo::make('Пользователь', 'user', resource: UserResource::class)
                    ->required()
                    ->searchable()
                    ->valuesQuery(static fn ($q) => $q->where('role', UserRole::Student->value)),
                BelongsTo::make('Группа', 'group', resource: StudentGroupResource::class)
                    ->required()
                    ->searchable(),
                Text::make('№ студ. билета', 'student_id_number')->required(),
            ]),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function rules(mixed $item): array
    {
        $profileId = $item?->getKey();

        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('student_profiles', 'user_id')->ignore($profileId),
            ],
            'group_id' => ['required', 'exists:student_groups,id'],
            'student_id_number' => [
                'required',
                'string',
                'max:64',
                Rule::unique('student_profiles', 'student_id_number')->ignore($profileId),
            ],
        ];
    }
}
