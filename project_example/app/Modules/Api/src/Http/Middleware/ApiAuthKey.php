<?php

namespace Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Проверяем несколько источников ключа для совместимости
        $key = $request->header('Authorization') 
            ?? $request->header('X-API-Key') 
            ?? $request->query('token');

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. API key is required in Authorization header, X-API-Key header, or token query parameter.'
            ], 401);
        }

        // Если ключ в формате "Bearer {token}", извлекаем токен
        if (str_starts_with($key, 'Bearer ')) {
            $key = substr($key, 7);
        }

        $validKey = env('API_KEY');

        if (!$validKey || $key !== $validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }

        return $next($request);
    }
}
