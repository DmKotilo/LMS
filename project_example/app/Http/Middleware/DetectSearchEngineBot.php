<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSearchEngineBot
{
    /**
     * Список User-Agent поисковых роботов
     */
    private array $botUserAgents = [
        // Google
        'Googlebot',
        'Googlebot-Image',
        'Googlebot-Video',
        'Googlebot-Mobile',
        'Mediapartners-Google',
        'AdsBot-Google',
        'FeedFetcher-Google',
        
        // Yandex
        'YandexBot',
        'YandexAccessibilityBot',
        'YandexMobileBot',
        'YandexDirectDyn',
        'YandexMetrika',
        'YandexImages',
        'YandexVideo',
        'YandexVideoParser',
        'YandexMedia',
        'YandexBlogs',
        'YandexFavicons',
        'YandexWebmaster',
        'YandexPagechecker',
        'YandexImageResizer',
        'YandexAdNet',
        'YandexDirect',
        'YaDirectFetcher',
        'YandexCalendar',
        'YandexSitelinks',
        'YandexNews',
        'YandexCatalog',
        'YandexAntivirus',
        'YandexMarket',
        'YandexVertis',
        'YandexForDomain',
        'YandexSpravBot',
        'YandexSearchShop',
        'YandexMedianaBot',
        'YandexOntoDB',
        'YandexOntoDBAPI',
        'YandexTurbo',
        
        // Bing
        'Bingbot',
        'BingPreview',
        
        // Другие
        'Slurp', // Yahoo
        'DuckDuckBot',
        'Baiduspider',
        'Sogou',
        'Exabot',
        'facebot', // Facebook
        'ia_archiver', // Alexa
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->header('User-Agent', '');
        
        $isBot = $this->isSearchEngineBot($userAgent);
        
        // Сохраняем информацию о боте в запросе
        $request->attributes->set('is_search_engine_bot', $isBot);
        $request->attributes->set('bot_user_agent', $isBot ? $userAgent : null);
        
        return $next($request);
    }

    /**
     * Проверка, является ли User-Agent поисковым роботом
     */
    private function isSearchEngineBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        foreach ($this->botUserAgents as $botAgent) {
            if (stripos($userAgent, $botAgent) !== false) {
                return true;
            }
        }

        return false;
    }
}

