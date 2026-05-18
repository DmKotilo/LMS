<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;

#[Group('Авторизация')]
#[Authenticated]
class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Endpoint(
        title: 'Выход',
        description: 'Удаляет текущий токен доступа.',
    )]
    #[ResponseFromFile('docs/responses/auth/logout.200.json')]
    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Выход выполнен успешно.',
        ]);
    }
}
