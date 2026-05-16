<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Middleware\CaptureUtmParameters;
use Illuminate\Http\Request;

final class UtmHelper
{
    /**
     * Извлекает UTM-метки: сначала из query (из ссылки), иначе из сессии.
     * Храним только в сессии, не в cookie — в ссылке они держатся за счёт редиректов.
     *
     * @return array<string, string>
     */
    public static function extract(Request $request): array
    {
        $keys = ['utm_campaign', 'utm_content', 'utm_medium', 'utm_source', 'utm_term'];
        $result = [];

        foreach ($keys as $key) {
            $queryValue = $request->query($key);
            if (!is_string($queryValue) || trim($queryValue) === '') {
                $queryValue = $request->query(strtoupper($key));
            }
            if (!is_string($queryValue)) {
                $queryValue = null;
            }

            $value = null;
            if ($queryValue !== null && trim($queryValue) !== '') {
                $value = trim($queryValue);
            } else {
                $sessionUtm = $request->session()->get(CaptureUtmParameters::SESSION_KEY, []);
                $sessionValue = is_array($sessionUtm) ? ($sessionUtm[$key] ?? null) : null;
                if (is_string($sessionValue) && trim($sessionValue) !== '') {
                    $value = trim($sessionValue);
                }
            }

            if ($value !== null && $value !== '') {
                $result[$key] = mb_substr($value, 0, 255);
            }
        }

        return $result;
    }
}
