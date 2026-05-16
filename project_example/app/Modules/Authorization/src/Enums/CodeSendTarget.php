<?php

namespace Authorization\Enums;

use App\Services\ActivityLogService;
use App\Support\UserBlockHelper;
use Authorization\Services\RegisterUserByPhoneService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Auth;
use Psy\Util\Json;
use User\Models\User;

enum CodeSendTarget
{

    case register; //отправка кода для регистрации
    case login; //отправка кода для логина
    case new_phone_verify; //отправка кода для верификации нового телефона
    case password_reset; //отправка кода для логина

    public static function get(string $name): self
    {
        foreach (self::cases() as $item) {
            if( $name === $item->name ){
                return $item;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class );
    }

    public function toString() : string
    {
        return match ($this) {
            self::register => 'новый',
            default => $this->value,
        };
    }

    public function redirectAfterSend($phone, $code) : RedirectResponse | JsonResponse
    {
        if (config('app.env') == 'local' || config('app.env') == 'develop') {
            $code = ": ".$code;
        } else {
            $code = "";
        }
        if(env('AUTH_INERTIA', true)) {
            return redirect()->back()->with([
                'success' => "Код отправлен".$code
            ]);
        } else {
            return match ($this) {
                self::register => redirect()->route('register-phone-confirm',  ['phone' => $phone])->with(['success' => "Код отправлен".$code]),
                self::login => redirect()->route('login-phone-confirm',  ['phone' => $phone])->with(['success' => "Код отправлен".$code]),
                self::new_phone_verify => redirect()->route('phone-confirm',  ['phone' => $phone])->with(['success' => "Код отправлен".$code]),
                self::password_reset => redirect()->route('forgot-password-phone-confirm',  ['phone' => $phone])->with(['success' => "Код отправлен".$code]),
                default => redirect()->back()->with(['success' => "Код отправлен"]),
            };
        }

    }

    public function redirectAfterValid($phone, $code, ?string $referralCode = null) : RedirectResponse
    {
        $request = request();
        $activityLogService = app(ActivityLogService::class);

        if ($this == self::register) {
            RegisterUserByPhoneService::registerUserByPhone($phone, $referralCode, $request);
        }
        if($this == self::new_phone_verify) {
            auth()->user()->update(['phone_verified_at' => Carbon::now()]);
        }
        if($this == self::login) {
            $user = User::where('phone', $phone)->first();
            if($user) {
                if (UserBlockHelper::isBlocked($user)) {
                    $activityLogService->logLogin($user, $request, false, $user->email);
                    return redirect()->route('login')->withErrors([
                        'blocked' => UserBlockHelper::blockMessage(),
                    ]);
                }
                Auth::login($user);
                // Логирование успешного входа через телефон
                $activityLogService->logLogin($user, $request, true, $user->email);
            } else {
                // Логирование неуспешной попытки входа через телефон
                $activityLogService->logLogin(null, $request, false, null);
                return redirect()->back()->with(['error' => 'Пользователь не найден']);
            }
        }

        if(env('AUTH_INERTIA', true)) {
            return match ($this) {
                self::register => redirect()->route('user.profile.index'),
                self::login => redirect()->route('user.profile.index'),
                self::new_phone_verify => redirect()->route('user.profile.index')->with(['success' => "Телефон подтвержден"]),
                self::password_reset => redirect()->route('reset-password-phone',  ['phone' => $phone, 'code' => $code])->with(['success' => "Код подтвержден. Пожалуйста, введите новый пароль"]),
                default => redirect()->back()->with(['success' => "Код подтвержден"]),
            };
        } else {
            return match ($this) {
                self::register => redirect()->route('main-page'),
                self::login => redirect()->route('main-page'),
                self::new_phone_verify => redirect()->route('main-page')->with(['success' => "Телефон подтвержден"]),
                self::password_reset => redirect()->route('reset-password-phone',  ['phone' => $phone, 'code' => $code])->with(['success' => "Код подтвержден. Пожалуйста, введите новый пароль"]),
                default => redirect()->back()->with(['success' => "Код подтвержден"]),
            };
        }
    }


}

