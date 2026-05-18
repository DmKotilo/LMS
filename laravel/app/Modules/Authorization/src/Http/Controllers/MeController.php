<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\AuthService;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Http\Resources\UserResource;

#[Group('Авторизация')]
#[Authenticated]
class MeController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Endpoint(title: 'Текущий пользователь')]
    #[ResponseFromFile('docs/responses/auth/me.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    public function __invoke(Request $request): UserResource
    {
        return new UserResource(
            $this->authService->loadRelationsForRole($request->user())
        );
    }
}
