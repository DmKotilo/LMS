<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use User\Enums\EducationForm;
use User\Models\StudentGroup;

/**
 * @extends ModelResource<StudentGroup>
 */
class StudentGroupResource extends ModelResource
{
    protected string $model = StudentGroup::class;

    protected string $title = 'Группы';

    protected string $column = 'name';

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'name'),
            Enum::make('Форма обучения', 'education_form')->attach(EducationForm::class),
            Number::make('Курс', 'course'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('Название', 'name')->required(),
                Enum::make('Форма обучения', 'education_form')->attach(EducationForm::class)->required(),
                Number::make('Курс', 'course')->min(1)->max(6)->required(),
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
            'name' => ['required', 'string', 'max:255'],
            'education_form' => ['required'],
            'course' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }
}
