<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\Gradebook\GradebookResource;
use App\MoonShine\Resources\User\StudentGroupResource;
use App\MoonShine\Resources\User\StudentProfileResource;
use App\MoonShine\Resources\User\UserResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

final class MoonShineLayout extends AppLayout
{
    protected function menu(): array
    {
        return [
            ...parent::menu(),
            MenuGroup::make('LMS', [
                MenuItem::make('Пользователи', UserResource::class),
                MenuItem::make('Группы', StudentGroupResource::class),
                MenuItem::make('Профили студентов', StudentProfileResource::class),
            ]),
            MenuGroup::make('Ведомости', [
                MenuItem::make('Ведомости', GradebookResource::class),
            ]),
        ];
    }
}
