<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Авторизация
 */
class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Выход
     *
     * Удаляет текущий токен доступа.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Выход выполнен успешно.',
        ]);
    }
}
