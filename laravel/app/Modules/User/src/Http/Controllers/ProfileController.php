<?php

namespace User\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Http\Requests\UpdateProfileRequest;
use User\Http\Resources\UserResource;
use User\Services\UserService;

#[Group('Общее', 'Эндпоинты, доступные любой авторизованной роли (студент, преподаватель, администратор).')]
#[Authenticated]
class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
    ) {}

    #[Endpoint(
        title: 'Просмотр профиля',
        description: 'В ответе поле `role` (`student` | `teacher` | `administrator`) и `role_label` — текущая роль пользователя.',
    )]
    #[ResponseFromFile('docs/responses/profile/show.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    public function show(Request $request): UserResource
    {
        return new UserResource(
            $this->authService->loadRelationsForRole($request->user())
        );
    }

    #[Endpoint(
        title: 'Обновление профиля',
        description: 'Можно изменить ФИО и телефон. Смена email: `POST /api/profile/email` (пароль + новый email, подтверждение по ссылке из письма).',
    )]
    #[BodyParam('last_name', 'string', 'Фамилия.', required: false, example: 'Иванов')]
    #[BodyParam('first_name', 'string', 'Имя.', required: false, example: 'Иван')]
    #[BodyParam('second_name', 'string', 'Отчество.', required: false, example: 'Иванович')]
    #[BodyParam('phone', 'string', 'Телефон.', required: false, example: '+79001234567')]
    #[ResponseFromFile('docs/responses/profile/update.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated(),
        );

        return new UserResource($user);
    }
}
