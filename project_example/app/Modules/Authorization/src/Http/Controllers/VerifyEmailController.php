<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {

        $user = $request->user();

        if ($user->new_email) {
            $user->email = $user->new_email;
            $user->new_email = null;
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return redirect()->intended(route('user.profile.index', absolute: false) . '?verified=1')->with('success', 'Почта успешно подтверждена!');;
    }
}
