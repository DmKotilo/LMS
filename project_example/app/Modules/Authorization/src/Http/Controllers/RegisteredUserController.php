<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Referral\src\Services\ReferralService;
use App\Services\ActivityLogService;
use User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('back/Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ReferralService $referralService, ActivityLogService $activityLogService): RedirectResponse
    {
        try {
            $request->validate([
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'referral_code' => ['nullable', 'string', 'exists:referrals,referral_code'],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // обработка рефералки
            $referralCode = $request->input('referral_code');
            $referralService->processReferral($user, $referralCode);

            event(new Registered($user));

            Auth::login($user);

            // Логирование успешной регистрации
            $activityLogService->logRegistration($user, $request, true);

            if(env('AUTH_INERTIA') == true)
                return redirect(route('user.profile.index', absolute: false))->with('success', 'Успешная регистрация');
            else
                return redirect(route('main-page', absolute: false))->with('success', 'Успешная регистрация');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Логирование неуспешной попытки регистрации
            $activityLogService->logRegistrationFailed($request, $e->errors());
            throw $e;
        }
    }

/*    public function storeByPhone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|max:255|unique:'.User::class,
            'code' => ['required', 'numeric', 'regex:/^([0-9]*)$/', 'digits:'.config('code_verification.code_length')],
        ]);
        $data['ip'] = $request->ip();
        $data['phone'] = preg_phone($data['phone']);

        try {
            $true_code = $this->codeService->check(
                code: $data['code'],
                subject: $data['phone'],
                ip: $data['ip'],
            );
        } catch (\Throwable $th) {
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }

        $user = User::create([
            'phone' => $data['phone'],
        ]);

        event(new Registered($user));

        Auth::login($user);
        return redirect(route('dashboard', absolute: false))->with('success', 'Успешная регистрация');
    }*/
}
