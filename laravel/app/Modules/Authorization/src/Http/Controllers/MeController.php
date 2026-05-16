<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\Request;
use User\Http\Resources\UserResource;

/**
 * @group Авторизация
 */
class MeController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Текущий пользователь
     */
    public function __invoke(Request $request): UserResource
    {
        return new UserResource(
            $this->authService->loadRelationsForRole($request->user())
        );
    }
}
