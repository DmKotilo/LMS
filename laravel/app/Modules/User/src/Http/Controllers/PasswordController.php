<?php

namespace User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use User\Http\Requests\UpdatePasswordRequest;
use User\Services\UserService;

/**
 * @group Профиль
 */
class PasswordController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Смена пароля
     */
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
