<?php

namespace App\Http\Middleware;

use App\Support\UserBlockHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use User\Models\User;

class LogoutBlockedUser
{
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {

        $currentUser = Auth::user();

        if (Auth::check() && UserBlockHelper::isBlocked($currentUser)) {
            $message = UserBlockHelper::blockMessage();

            Auth::guard('web')->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'blocked' => $message,
                    ],
                ], 403, options: JSON_UNESCAPED_UNICODE);
            }

            return redirect()->route('login')->withErrors([
                'blocked' => $message,
            ]);
        }

        return $next($request);
    }
}

