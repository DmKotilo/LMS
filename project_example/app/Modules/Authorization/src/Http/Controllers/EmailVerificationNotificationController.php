<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyNewEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('user.profile.index', absolute: false));
        }
        $incomingEmail = $request->input('email');

        if ($incomingEmail !== $user->email) {
            $user->new_email = $incomingEmail;
            $user->save();

            $user->notify(new VerifyNewEmail($user->new_email));


            return back()->with('status', 'verification-link-sent');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
