<?php

namespace User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Http\Requests\UpdatePasswordRequest;
use User\Services\UserService;

#[Group('Общее', 'Эндпоинты, доступные любой авторизованной роли (студент, преподаватель, администратор).')]
#[Authenticated]
class PasswordController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[Endpoint(title: 'Смена пароля')]
    #[BodyParam('current_password', 'string', 'Текущий пароль.', example: 'old-password')]
    #[BodyParam('password', 'string', 'Новый пароль (не менее 8 символов).', example: 'new-password')]
    #[BodyParam('password_confirmation', 'string', 'Подтверждение нового пароля.', example: 'new-password')]
    #[ResponseFromFile('docs/responses/profile/password.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        $this->userService->updatePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        return response()->json([
            'message' => 'Пароль успешно изменён.',
        ]);
    }
}
