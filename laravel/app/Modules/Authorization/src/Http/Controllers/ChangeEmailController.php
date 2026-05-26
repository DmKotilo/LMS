<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyNewEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Http\Resources\UserResource;

#[Group('Общее', 'Эндпоинты, доступные любой авторизованной роли (студент, преподаватель, администратор).')]
class ChangeEmailController extends Controller
{
    #[Authenticated]
    #[Endpoint(
        title: 'Смена email',
        description: 'Сохраняет новый адрес в `new_email` и отправляет письмо с подтверждением. Текущий `email` не меняется, пока пользователь не перейдёт по ссылке из письма.',
    )]
    #[BodyParam('email', 'string', 'Новый email.', example: 'new.user@example.com')]
    #[BodyParam('password', 'string', 'Текущий пароль.', example: 'password')]
    #[ResponseFromFile('docs/responses/profile/email.200.json')]
    #[ResponseFromFile('docs/responses/errors/422.json', status: 422)]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::notIn([$user->email]),
            ],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Укажите новый email.',
            'email.email' => 'Укажите корректный email.',
            'email.unique' => 'Этот email уже используется.',
            'email.not_in' => 'Новый email совпадает с текущим.',
            'password.required' => 'Укажите текущий пароль.',
        ]);

        if (! Hash::check($validated['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => ['Неверный пароль.'],
            ]);
        }

        $newEmail = $validated['email'];

        $user->update([
            'new_email' => $newEmail,
            'email_verified_at' => null,
        ]);

        $user->notify(new VerifyNewEmail($newEmail));

        return response()->json([
            'message' => 'Письмо с подтверждением отправлено на новый адрес.',
            'user' => new UserResource($user->fresh(['studentProfile.group'])),
        ]);
    }
}
