<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyNewEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Смена почты')]

class ChangeEmailController extends Controller
{

    #[Endpoint('Смена почты из профиля')]
    #[BodyParam('password', description: 'Текущий пароль пользователя', required: true, example: 'secret')]
    #[BodyParam('email', description: 'Новый адрес электронной почты', required: true, example: 'new@example.com')]
    #[Response(description: 'Пользователь перенаправлен обратно с флагом "verification-link-sent"')]
    #[Response(description: 'Неверный пароль или email уже используется')]
    public function store(Request $request)
    {
        $user = $request->user();

        $hasPassword = !empty($user->password);

        $rules = [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ];

        if ($hasPassword) {
            $rules['password'] = ['required', 'string'];
        }

        $request->validate($rules);

        if ($hasPassword && !Hash::check($request->password, $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => ['Неверный пароль.'],
            ]);
        }

        $user->update([
            'new_email' => $request->email,
            'email_verified_at' => null,
        ]);

        $user->notify(new VerifyNewEmail($user->new_email));

        return back()->with('status', 'verification-link-sent');
    }



}

