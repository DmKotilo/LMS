<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Http\Requests\LoginRequest;
use Authorization\Http\Resources\AuthTokenResource;
use Authorization\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @group Авторизация
 *
 * @unauthenticated
 */
class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Вход
     *
     * Авторизация по email и паролю. Возвращает Bearer-токен (Sanctum).
     */
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
