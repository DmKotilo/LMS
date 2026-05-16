<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Closure;

class EnsureVerified extends EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $emailVerified = true;
        $phoneVerified = true;

        // проверка email
        if ($user->email && $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $emailVerified = false;
        }

        // проверка телефона
        if ($user->phone && is_null($user->phone_verified_at)) {
            $phoneVerified = false;
        }

        // если есть email, но не верифицирован
        if ($user->email && ! $emailVerified) {
            return $request->expectsJson()
                ? abort(403, 'Ваш email не подтверждён.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        // если есть телефон, но не верифицирован (todo)
        // if ($user->phone && ! $phoneVerified) {
        //     return $request->expectsJson()
        //         ? abort(403, 'Ваш телефон не подтверждён.')
        //         : Redirect::guest(URL::route($redirectToRoute ?: 'phone-verify'));
        // }

        // если нет ни email, ни телефона, но есть соцсеть — разрешаем
        if (! $user->email && ! $user->phone) {
            if ($user->vk_id || $user->yandex_id) {
                return $next($request);
            }

            // иначе запрещаем (аккаунт без идентификаторов)
            return $request->expectsJson()
                ? abort(403, 'Этот аккаунт не имеет данных для входа (телефон, email или соцсеть).')
                : abort(403, 'Этот аккаунт не имеет данных для входа (телефон, email или соцсеть).');
        }

        return $next($request);
    }
}

