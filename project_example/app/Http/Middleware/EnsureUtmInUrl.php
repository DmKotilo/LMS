<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUtmInUrl
{
    /**
     * При каждой навигации через Inertia (X-Inertia) делаем редирект на тот же URL —
     * полная перезагрузка, dataLayer сбрасывается. Так и с UTM, и без UTM поведение одинаковое.
     * После заказа (ecommerce_order_data) не редиректим.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        if (
            $request->is('admin/*')
            || $request->is('storage/*')
            || $request->is('build/*')
            || $request->is('docs/*')
            || $request->is('api/*')
        ) {
            return $next($request);
        }

        if ($request->session()->has('ecommerce_order_data')) {
            return $next($request);
        }

        // Исключаем order.create из перезагрузки, так как это динамическая страница
        $path = $request->path();
        if ($path === 'order/create' || str_starts_with($path, 'order/create') || $request->routeIs('order.create')) {
            return $next($request);
        }

        if ($request->header('X-Inertia')) {
            return Inertia::location($request->fullUrl());
        }

        return $next($request);
    }
}
