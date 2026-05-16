<?php

namespace Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Bitrix24AuthKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->query('token');
        
        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Key parameter is required.'
            ], 401);
        }

        $validKey = env('BITRIX24_API_KEY');

        if (!$validKey || $key !== $validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }

        return $next($request);
    }
}

