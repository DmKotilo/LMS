<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Authorization\Services\SMSService;
use Authorization\Services\VerifyCodeService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use User\Models\User;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */

    public function __construct(
        public VerifyCodeService $codeService,
        private SMSService $sms,
    ) {
    }

    public function create(Request $request): Response
    {
        return Inertia::render('back/Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ActivityLogService $activityLogService): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request, $activityLogService) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                // Логирование успешного изменения пароля
                $activityLogService->logPasswordReset($user, $request, true);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('main-page')->with('status', __($status));
        }

        // Логирование неуспешного изменения пароля
        if ($user) {
            $activityLogService->logPasswordReset($user, $request, false);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function storeByPhone(Request $request, ActivityLogService $activityLogService): RedirectResponse
    {

        $request->merge(['phone' => \App\Helpers\PhoneHelper::formatPhone($request->phone)]);

        $data = $request->validate([
            'code' => 'required',
            'phone' => 'required|exists:User\Models\User,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $data['ip'] = $request->ip();

        try {
            $true_code = $this->codeService->check(
                code: $data['code'],
                subject: $data['phone'],
                ip: $data['ip'],
            );
        } catch (\Throwable $th) {
            // Логирование неуспешной попытки изменения пароля
            $user = User::where('phone', $data['phone'])->first();
            if ($user) {
                $activityLogService->logPasswordReset($user, $request, false);
            }
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }

        $user = User::where('phone', $data['phone'])->first();
        if(!$user){
            return redirect()->back()->with(['error' => "Пользователь с таким телефоном не найден"]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        // Логирование успешного изменения пароля
        $activityLogService->logPasswordReset($user, $request, true);

        return redirect()->route('main-page')->with('success', __(Password::PASSWORD_RESET))->with('status', __(Password::PASSWORD_RESET));

    }
}
