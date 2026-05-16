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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Referral\Models\Referral;
use User\Models\User;

class VkontakteController extends Controller
{
    public function get()
    {
        return Socialite::driver('vkontakte')->redirect();
    }

    public function callback(Request $request, ActivityLogService $activityLogService)
    {
        $data = $request->validate([
            "refresh_token" => "required",
            "access_token" => "required",
            "id_token" => "required",
            "token_type" => "required",
            "expires_in" => "required",
            "user_id" => "required",
            "state" => "required",
            "scope" => "required",
        ]);

        $response = Http::asForm()->post('https://id.vk.com/oauth2/user_info', [
            'client_id'     => config("services.vkontakte.client_id"),
            'access_token'  => $data['access_token'],
        ]);

        $info = $response->json();

        if (isset($info['error'])) {
            // Логирование неуспешной попытки OAuth входа
            $activityLogService->logSocialAuth(null, $request, UserActivityLog::PROVIDER_VKONTAKTE, false);

            Log::error('VK OAuth: Ошибка получения информации о пользователе', [
                'error' => $info['error'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $data['user_id'] ?? null,
            ]);

            return response()->json(['success' => false], 401);
        }

        $userInfo = $info['user'];

        // если клиент есть в нашей системе
        if (Auth::check()) {
            $user = Auth::user();

            // проверим, что этот вк еще не привязан к другому
            $vkExists = User::where('vk_id', $userInfo["user_id"])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($vkExists) {
                return response()->json([
                    "success" => false,
                    "error" => "Этот VK аккаунт уже используется другим пользователем"
                ]);
            }

            // привязываем vk_id
            $user->update([
                'vk_id' => $userInfo["user_id"]
            ]);

            if ($user->name == null){
                $user->update([
                    'name' => $userInfo["first_name"],
                ]);
                $user->save();
            }
            if ($user->last_name == null){
                $user->update([
                    'last_name' => $userInfo['last_name'],
                ]);
                $user->save();
            }

            return response()->json(["success" => true, "linked" => true]);
        }

        // ищем пользователя сначала по vk_id
        $user = User::where('vk_id', $userInfo["user_id"])->first();

        if (!$user && !empty($userInfo['email'])) {
            $user = User::where('email', $userInfo['email'])->first();

            if ($user) {
                // если нашли по email, просто привязываем VK
                $user->update([
                    'vk_id' => $userInfo["user_id"],
                ]);
                if ($user->name == null){
                    $user->update([
                        'name' => $userInfo["first_name"],
                    ]);
                    $user->save();
                }
                if ($user->last_name == null){
                    $user->update([
                        'last_name' => $userInfo['last_name'],
                    ]);
                    $user->save();
                }
            }
        }

        // если все еще не нашли — создаем нового
        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'name' => $userInfo["first_name"],
                'last_name' => $userInfo['last_name'],
                'email' => $userInfo['email'] ?? null,
                'email_verified_at' => !empty($userInfo['email']) ? now() : null,
                'vk_id' => $userInfo["user_id"],
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
            $activityLogService->logSocialAuth($user, $request, UserActivityLog::PROVIDER_VKONTAKTE, false);

            Log::warning('VK OAuth: Попытка входа заблокированного пользователя', [
                'user_id' => $user->id,
                'vk_id' => $userInfo["user_id"],
                'email' => $user->email,
                'ip' => $request->ip(),
                'block_message' => UserBlockHelper::blockMessage(),
            ]);

            return response()->json([
                'success' => false,
                'errors' => [
                    'blocked' => UserBlockHelper::blockMessage(),
                ],
            ], 403, options: JSON_UNESCAPED_UNICODE);
        }

        Auth::login($user);

        // Логирование успешного OAuth входа
        $activityLogService->logSocialAuth($user, $request, UserActivityLog::PROVIDER_VKONTAKTE, true, $isNewUser);

        return response()->json(["success" => true, "registered" => true]);
    }


    public function detach()
    {
        $user = Auth::user();

        if ($user->vk_id) {
            $user->update(['vk_id' => null]);
        }

        return back()->with('success', 'VK аккаунт отвязан.');
    }


}
