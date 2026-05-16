<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CaptureUtmParameters
{
    public const SESSION_KEY = 'utm_params';

    private const UTM_KEYS = [
        'utm_campaign',
        'utm_content',
        'utm_medium',
        'utm_source',
        'utm_term',
    ];

    /**
     * Если UTM пришли в URL — сохраняем в сессию.
     * Дальше EnsureUtmInUrl будет подставлять их в ссылку редиректом, UTM остаются в URL до заказа/формы.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $captured = [];

        foreach ($request->query() as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $normalizedKey = mb_strtolower((string) $key);
            if (!in_array($normalizedKey, self::UTM_KEYS, true)) {
                continue;
            }
            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                continue;
            }
            $captured[$normalizedKey] = mb_substr($stringValue, 0, 255);
        }

        if (!empty($captured)) {
            $request->session()->put(self::SESSION_KEY, $captured);
        }

        return $next($request);
    }
}
