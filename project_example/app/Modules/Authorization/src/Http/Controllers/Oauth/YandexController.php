<?php

namespace Authorization\Http\Controllers\Oauth;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use App\Services\ActivityLogService;
use App\Support\UserBlockHelper;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Referral\Models\Referral;
use User\Models\User;

class YandexController extends Controller
{

    public function get()
    {
        return Socialite::driver('yandex')->redirect();
    }
    public function callback(Request $request, ActivityLogService $activityLogService)
    {
        try {
            $yandexUser = Socialite::driver('yandex')->user();
        } catch (\Exception $e) {
            // Логирование неуспешной попытки OAuth входа
            $activityLogService->logSocialAuth(null, $request, UserActivityLog::PROVIDER_YANDEX, false);
            return redirect()->route("main-page")
                ->with("error", "Ошибка авторизации через Яндекс");
        }

        $userInfo = [
            "user_id"    => $yandexUser->getId(),
            "first_name" => $yandexUser->user['first_name'] ?? null,
            "last_name"  => $yandexUser->user['last_name'] ?? null,
            "email"      => $yandexUser->getEmail(),
        ];

        // если клиент есть в нашей системе
        if (Auth::check()) {
            $user = Auth::user();

            $yaExists = User::where('yandex_id', $userInfo["user_id"])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($yaExists) {
                return redirect()->route("main-page")
                    ->with("error", "Этот Яндекс аккаунт уже привязан к другому пользователю");
            }

            // привязываем yandex_id
            $user->update([
                'yandex_id' => $userInfo["user_id"],
            ]);
            if ($user->name == null){
                $user->update([
                    'name' => $userInfo["first_name"],
                ]);
                $user->save();
            }
            if ($user->last_name == null){
                $user->update([
                    'last_name' => $userInfo["last_name"],
                ]);
                $user->save();
            }

            return redirect()->route("user.profile.edit")
                ->with("success", "Яндекс аккаунт успешно привязан");
        }

        // ищем по yandex_id
        $user = User::where('yandex_id', $userInfo["user_id"])->first();

        if (!$user && !empty($userInfo['email'])) {
            $user = User::where('email', $userInfo['email'])->first();

            if ($user) {
                $user->update([
                    'yandex_id' => $userInfo["user_id"],
                ]);
                if ($user->name == null){
                    $user->update([
                        'name' => $userInfo["first_name"],
                    ]);
                    $user->save();
                }
                if ($user->last_name == null){
                    $user->update([
                        'last_name' => $userInfo["last_name"],
                    ]);
                    $user->save();
                }
            }
        }

        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'name'  => $userInfo["first_name"],
                'last_name' => $userInfo["last_name"],
                'email' => $userInfo['email'] ?? null,
                'email_verified_at' => !empty($userInfo['email']) ? now() : null,
                'yandex_id' => $userInfo["user_id"],
                'password' => null, // или null, если поддерживается
            ]);

            // если при регистрации есть реферальный код
            if ($referralCode = session('referral_code')) {
                $referral = Referral::where('referral_code', $referralCode)
                    ->whereNull('invited_id')
                    ->first();

                if ($referral) {
                    if ($referral->inviter_id === $user->id) {
                        return;
                    }

                    $alreadyInvited = Referral::where('invited_id', $user->id)->exists();

                    if ($alreadyInvited) {
                        return;
                    }

                    $referral->update(['invited_id' => $user->id]);
                }
            }

        }

        if (UserBlockHelper::isBlocked($user)) {
            $activityLogService->logSocialAuth($user, $request, UserActivityLog::PROVIDER_YANDEX, false);
            return redirect()->route('login')->withErrors([
                'blocked' => UserBlockHelper::blockMessage(),
            ]);
        }

        Auth::login($user);

        // Логирование успешного OAuth входа
        $activityLogService->logSocialAuth($user, $request, UserActivityLog::PROVIDER_YANDEX, true, $isNewUser);

        return redirect()->route("user.profile.edit")
            ->with("success", "Регистрация через Яндекс успешна");
    }


    public function detach()
    {
        $user = Auth::user();

        if ($user->yandex_id) {
            $user->update(['yandex_id' => null]);
        }

        return back()->with('success', 'Яндекс аккаунт отвязан.');
    }


}
