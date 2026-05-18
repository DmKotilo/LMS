<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Http\Requests\LoginRequest;
use Authorization\Http\Resources\AuthTokenResource;
use Authorization\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Авторизация')]
#[Unauthenticated]
class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Endpoint(
        title: 'Вход',
        description: <<<'DESC'
Авторизация по email и паролю. Возвращает Bearer-токен (Sanctum).

В объекте `user` приходит роль:
- `role`: `student` | `teacher` | `administrator`
- `role_label`: человекочитаемое название
- `default_path`: стартовый путь API для этой роли (`/api/student/results`, `/api/gradebooks` и т.д.)

По роли фронтенд выбирает раздел приложения; отдельного «типа входа» нет — один endpoint для всех.
DESC,
    )]
    #[BodyParam('email', 'string', 'Email пользователя.', example: 'teacher@example.com')]
    #[BodyParam('password', 'string', 'Пароль.', example: 'password')]
    #[ResponseFromFile('docs/responses/auth/login.200.json')]
    #[ResponseFromFile('docs/responses/auth/login.422.json', status: 422)]
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
        );

        return (new AuthTokenResource($result))
            ->response()
            ->setStatusCode(200);
    }
}
