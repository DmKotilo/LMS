<?php

namespace User\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\Request;
use User\Http\Requests\UpdateProfileRequest;
use User\Http\Resources\UserResource;
use User\Services\UserService;

/**
 * @group Профиль
 */
class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
    ) {}

    /**
     * Просмотр профиля
     */
    public function show(Request $request): UserResource
    {
        return new UserResource(
            $this->authService->loadRelationsForRole($request->user())
        );
    }

    /**
     * Обновление профиля
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated(),
        );

        return new UserResource($user);
    }
}
