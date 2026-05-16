<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для принудительной полной перезагрузки страницы при Inertia запросах.
 * Это решает проблему с накоплением dataLayer в Яндекс.Метрике.
 */
class ForceFullPageReload
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если это Inertia AJAX запрос (SPA навигация) И это GET запрос
        if ($request->header('X-Inertia') && $request->isMethod('GET')) {

            // Специальная обработка для order.create — проверяем ПЕРВОЙ, до всех остальных:
            // Всегда исключаем order.create из перезагрузки, так как это динамическая страница.
            // Проверяем несколькими способами для надёжности (путь, имя роута).
            $path = $request->path();

            // По пути (работает даже если роут не определён)
            if ($path === 'order/create' || str_starts_with($path, 'order/create')) {
                Log::debug('ForceFullPageReload: order.create excluded by path check', [
                    'path' => $path,
                    'url' => $request->fullUrl(),
                    'route_name' => $request->route()?->getName(),
                ]);
                return $next($request);
            }

            // По имени роута (если роут уже определён)
            try {
                if ($request->routeIs('order.create')) {
                    return $next($request);
                }
            } catch (\Throwable $e) {
                // Игнорируем ошибки при проверке роута
            }

            // Специальная обработка для главной страницы (main-page):
            if ($request->routeIs('main-page') || $request->path() === '/') {
                return $next($request);
            }

            // Реферальные роуты
            if ($request->is('referral/*')) {
                return $next($request);
            }

            // catalog.index
            if ($request->routeIs('catalog.index')) {
                if ($request->session()->has('success')) {
                    return $next($request);
                }
                $response = response('', 409);
                $response->headers->set('X-Inertia-Location', $request->fullUrl());
                return $response;
            }

            // cart.index
            // - Если есть flash сообщение (редирект после применения промокода и т.д.) - не делаем перезагрузку
            // - Если нет flash сообщения (обычный переход) - делаем перезагрузку
            if ($request->routeIs('cart.index')) {
                // Проверяем наличие flash сообщений, которые указывают на редирект после действия
                if ($request->session()->has('success') || 
                    $request->session()->has('error') || 
                    $request->session()->has('message') ||
                    $request->session()->has('applied_promocode')) {
                    // Это редирект после действия (применение промокода и т.д.) - не делаем перезагрузку
                    return $next($request);
                } else {
                    // Это обычный переход на корзину - делаем полную перезагрузку
                    $response = response('', 409);
                    $response->headers->set('X-Inertia-Location', $request->fullUrl());
                    return $response;
                }
            }
            
            // Исключения для маршрутов, которые используются для AJAX запросов или динамического обновления
            $excludedRoutes = [
                'main-page',                          // Главная страница (редиректы после авторизации/регистрации)
                // order.create обрабатывается отдельно выше
                'order.calculate',                    // Расчёт заказа (AJAX)
                'order.store_get',                    // Получение данных для заказа (AJAX)
                'order.pickup-location-by-city.get',  // Получение пунктов самовывоза (AJAX)
                'catalog.search.autocomplete',        // Автокомплит поиска (AJAX)
                'cart.checkGift',                     // Проверка подарка (POST, но на всякий случай)
                
                // Роуты авторизации и регистрации
                'login',                              // Страница входа
                'register',                           // Страница регистрации
                'register-phone',                     // Регистрация по телефону
                'register-phone-confirm',             // Подтверждение регистрации по телефону
                'login-phone',                        // Вход по телефону
                'login-phone-confirm',                 // Подтверждение входа по телефону
                'password.request',                    // Забыли пароль
                'forgot-password-phone',              // Забыли пароль (телефон)
                'forgot-password-phone-confirm',      // Подтверждение восстановления пароля (телефон)
                'reset-password-phone',               // Сброс пароля (телефон)
                'password.reset',                     // Сброс пароля
                'password.confirm',                    // Подтверждение пароля
                'phone-verify',                       // Подтверждение телефона
                'phone-confirm',                      // Подтверждение телефона (подтверждение кода)
                'verification.notice',                 // Уведомление о верификации email
                'verification.verify',                 // Верификация email
                'verification.send',                   // Отправка письма верификации email (POST, но на всякий случай)
                'vkontakte.get',                       // OAuth ВКонтакте
                'yandex.get',                         // OAuth Яндекс
                'code.send',                          // Отправка SMS кода
                'code.confirm',                       // Подтверждение SMS кода
                
                // Роуты профиля
                'user.profile.index',                      // Главная страница профиля
                'user.profile.privilege.program',          // Программа привилегий
                'user.profile.bonus.history',              // История бонусов
                'user.profile.notifications',               // Уведомления
                'user.profile.delete.page',                // Страница удаления аккаунта
                'user.profile.orders_history',             // История заказов
                'user.profile.orders.history.show',        // Просмотр заказа
                'user.profile.white_list',                 // Белый список
                'user.profile.reviews',                    // Отзывы
                'user.profile.edit',                       // Редактирование профиля
                'user.profile.settings',                   // Настройки профиля
                'user.profile.logout',                     // Выход из профиля
                'user.verification.verify',                // Верификация email (альтернативный роут)
            ];
            
            // Если это не исключенный маршрут - делаем полную перезагрузку
            if (!$request->routeIs($excludedRoutes)) {
                Log::debug('ForceFullPageReload: forcing full page reload', [
                    'path' => $path,
                    'url' => $request->fullUrl(),
                    'route_name' => $request->route()?->getName(),
                    'excluded_routes' => $excludedRoutes,
                ]);

                $response = response('', 409);
                $response->headers->set('X-Inertia-Location', $request->fullUrl());
                return $response;
            }
        }

        return $next($request);
    }
}

