<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BreadcrumbService
{
    /**
     * Генерация хлебных крошек на основе текущего маршрута
     *
     * @param Request $request
     * @return array
     */
    public function generate(Request $request): array
    {
        $breadcrumbs = [
            [
                'name' => 'Главная',
                'url' => route('main-page'),
            ]
        ];

        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Если это главная страница, возвращаем только один элемент
        if ($routeName === 'main-page' || $path === '/') {
            return $breadcrumbs;
        }

        // Проверяем, есть ли кастомные breadcrumbs
        $customBreadcrumbs = $this->getCustomBreadcrumbs($request);
        if ($customBreadcrumbs) {
            return $customBreadcrumbs;
        }

        // Получаем дополнительные крошки на основе маршрута
        $additional = $this->getBreadcrumbsForRoute($routeName, $request);

        return array_merge($breadcrumbs, $additional);
    }

    /**
     * Получить хлебные крошки для конкретного маршрута
     *
     * @param string|null $routeName
     * @param Request $request
     * @return array
     */
    protected function getBreadcrumbsForRoute(?string $routeName, Request $request): array
    {
        if (!$routeName) {
            return $this->getBreadcrumbsFromUrl($request);
        }

        // Маппинг маршрутов на хлебные крошки
        $routeMap = [
            // Каталог
            'catalog.index' => function() use ($request) {
                $category = $request->route('category');

                // Если категория не указана, показываем просто "Каталог"
                if (!$category) {
                    return [
                        ['name' => 'Каталог', 'url' => route('catalog.index')],
                    ];
                }

                // Получаем категорию из БД
                $categoryObj = null;
                if (is_string($category)) {
                    $categorySlugs = explode('/', $category);
                    $lastSlug = end($categorySlugs);
                    $categoryObj = \Category\Models\Category::where('slug', $lastSlug)->first();
                } elseif (is_object($category)) {
                    $categoryObj = $category;
                }

                $breadcrumbs = [
                    ['name' => 'Каталог', 'url' => route('catalog.index')],
                ];

                if ($categoryObj) {
                    // Строим breadcrumbs через parent цепочку
                    $path = [];
                    $current = $categoryObj;

                    // Собираем всех родителей
                    while ($current) {
                        $path[] = $current;
                        $current = $current->parent ?? null;
                    }

                    // Переворачиваем чтобы идти от корня к листу
                    $path = array_reverse($path);

                    // Добавляем все категории кроме последней (она будет текущей)
                    for ($i = 0; $i < count($path) - 1; $i++) {
                        $breadcrumbs[] = [
                            'name' => $path[$i]->name,
                            'url' => route('catalog.index', ['category' => $path[$i]->slug]),
                        ];
                    }

                    // Добавляем текущую категорию
                    $breadcrumbs[] = [
                        'name' => $categoryObj->name,
                        'url' => route('catalog.index', ['category' => $categoryObj->slug]),
                    ];
                }

                return $breadcrumbs;
            },
            'catalog.product.show' => function() use ($request) {
                $product = $request->route('product');
                $breadcrumbs = [
                    ['name' => 'Каталог', 'url' => route('catalog.index')],
                ];

                // Маршрут может передать slug (string) вместо модели — загружаем продукт
                if (is_string($product)) {
                    $product = \Product\Models\Product::where('slug', $product)->first();
                }

                if ($product && is_object($product)) {
                    // Загружаем категории если еще не загружены
                    if (!$product->relationLoaded('categories')) {
                        $product->load('categories');
                    }

                    // Добавляем все категории продукта (они идут от родительской к дочерней)
                    // Сортируем по lvl чтобы гарантировать правильный порядок
                    $categories = $product->categories->sortBy('lvl');

                    foreach ($categories as $category) {
                        $breadcrumbs[] = [
                            'name' => $category->name,
                            'url' => route('catalog.index', ['category' => $category->slug]),
                        ];
                    }

                    $breadcrumbs[] = [
                        'name' => $product->name,
                        'url' => route('catalog.product.show', $product->slug),
                    ];
                }

                return $breadcrumbs;
            },

            // Заказ (оформление)
            'order.create' => [
                ['name' => 'Оформление заказа', 'url' => route('order.create')],
            ],

            // Рецепты
            'recipe.index' => [
                ['name' => 'Рецепты', 'url' => route('recipe.index')],
            ],
            'recipe.show' => function() use ($request) {
                $recipe = $request->route('recipe');

                $breadcrumbs = [
                    ['name' => 'Рецепты', 'url' => route('recipe.index')],
                ];

                if ($recipe) {
                    $breadcrumbs[] = [
                        'name' => $recipe->title ?? $recipe->name ?? 'Рецепт',
                        'url' => route('recipe.show', $recipe->id),
                    ];
                }

                return $breadcrumbs;
            },

            // Профиль (префикс user.profile.*)
            'user.profile.index' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
            ],
            'user.profile.edit' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Редактирование профиля', 'url' => route('user.profile.edit')],
            ],
            'user.profile.settings' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Настройки', 'url' => route('user.profile.settings')],
            ],
            'user.profile.orders_history' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'История заказов', 'url' => route('user.profile.orders_history')],
            ],
            'user.profile.orders.history.show' => function() use ($request) {
                $order = $request->route('order');
                return [
                    ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                    ['name' => 'История заказов', 'url' => route('user.profile.orders_history')],
                    ['name' => 'Заказ №' . ($order->id ?? ''), 'url' => route('user.profile.orders.history.show', $order->id ?? 0)],
                ];
            },
            'user.profile.privilege.program' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Программа привилегий', 'url' => route('user.profile.privilege.program')],
            ],
            'user.profile.bonus.history' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Программа привилегий', 'url' => route('user.profile.privilege.program')],
                ['name' => 'История бонусов', 'url' => route('user.profile.bonus.history')],
            ],
            'user.profile.white_list' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Избранное', 'url' => route('user.profile.white_list')],
            ],
            'user.profile.reviews' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Мои отзывы', 'url' => route('user.profile.reviews')],
            ],
            'user.profile.notifications' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Уведомления', 'url' => route('user.profile.notifications')],
            ],
            'user.profile.delete.page' => [
                ['name' => 'Личный кабинет', 'url' => route('user.profile.index')],
                ['name' => 'Удаление аккаунта', 'url' => route('user.profile.delete.page')],
            ],

            // Меню
            'menu-page' => [
                ['name' => 'Меню', 'url' => route('menu-page')],
            ],

            // Рецепты
            'recipes-page' => [
                ['name' => 'Рецепты', 'url' => route('recipes-page')],
            ],

            // Акции
            'promo-page' => [
                ['name' => 'Акции', 'url' => route('promo-page')],
            ],

            // Доставка
            'delivery-page' => [
                ['name' => 'Доставка и оплата', 'url' => route('delivery-page')],
            ],

            // Личный кабинет (старый маршрут dashboard)
            'dashboard' => [
                ['name' => 'Личный кабинет', 'url' => route('dashboard')],
            ],

            // Новости/Статьи (префикс blog.*)
            'blog.index' => [
                ['name' => 'Новости', 'url' => route('blog.index')],
            ],
            'blog.selection.show' => function() use ($request) {
                $selection = $request->route('selection');
                
                return [
                    ['name' => 'Новости', 'url' => route('blog.index')],
                    ['name' => $selection->name ?? 'Подборка', 'url' => route('blog.selection.show', $selection->id ?? 0)],
                ];
            },
            'blog.article.show' => function() use ($request) {
                $article = $request->route('article');
                
                return [
                    ['name' => 'Новости', 'url' => route('blog.index')],
                    ['name' => $article->title ?? $article->name ?? 'Статья', 'url' => route('blog.article.show', $article->id ?? 0)],
                ];
            },

            // Акции (префикс promotion.*)
            'promotion.index' => [
                ['name' => 'Акции', 'url' => route('promotion.index')],
            ],
            'promotion.show' => function() use ($request) {
                $promotion = $request->route('promotion');
                
                return [
                    ['name' => 'Акции', 'url' => route('promotion.index')],
                    ['name' => $promotion->name ?? $promotion->title ?? 'Акция', 'url' => route('promotion.show', $promotion->id ?? 0)],
                ];
            },

            // Страницы (префикс page.page.* - двойной префикс!)
            'page.page.about-us' => [
                ['name' => 'О компании', 'url' => route('page.page.about-us')],
            ],
            'page.page.show' => function() use ($request) {
                $slug = $request->route('slug');
                $page = null;
                
                // Пытаемся получить страницу из БД
                if ($slug) {
                    $page = \Pages\Models\Page::where('slug', $slug)->first();
                }
                
                return [
                    ['name' => $page?->name ?? $page?->title ?? Str::title(str_replace('-', ' ', $slug ?? 'Страница')), 'url' => route('page.page.show', $slug ?? '')],
                ];
            },
        ];

        // Проверяем точное совпадение
        if (isset($routeMap[$routeName])) {
            $result = $routeMap[$routeName];
            return is_callable($result) ? $result() : $result;
        }

        // Проверяем совпадение по началу имени маршрута (например, для вложенных маршрутов)
        foreach ($routeMap as $pattern => $crumbs) {
            if (Str::startsWith($routeName, $pattern . '.')) {
                return is_callable($crumbs) ? $crumbs() : $crumbs;
            }
        }

        // Если маршрут не найден, пытаемся сгенерировать из URL
        return $this->getBreadcrumbsFromUrl($request);
    }

    /**
     * Генерация хлебных крошек из URL (фолбэк)
     *
     * @param Request $request
     * @return array
     */
    protected function getBreadcrumbsFromUrl(Request $request): array
    {
        $breadcrumbs = [];
        $segments = $request->segments();
        $url = '';

        foreach ($segments as $segment) {
            $url .= '/' . $segment;

            // Пропускаем числовые сегменты (обычно это ID)
            if (is_numeric($segment)) {
                continue;
            }

            $breadcrumbs[] = [
                'name' => $this->formatSegmentName($segment),
                'url' => $url, // Всегда передаем URL, включая последний элемент
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Форматирование имени сегмента URL
     *
     * @param string $segment
     * @return string
     */
    protected function formatSegmentName(string $segment): string
    {
        // Заменяем дефисы на пробелы и делаем первую букву заглавной
        return Str::title(str_replace(['-', '_'], ' ', $segment));
    }

    /**
     * Добавить пользовательские хлебные крошки
     *
     * Этот метод можно вызывать из контроллеров для задания кастомных крошек
     *
     * @param array $breadcrumbs
     * @return void
     */
    public static function set(array $breadcrumbs): void
    {
        request()->attributes->set('custom_breadcrumbs', $breadcrumbs);
    }

    /**
     * Получить пользовательские хлебные крошки
     *
     * @param Request $request
     * @return array|null
     */
    protected function getCustomBreadcrumbs(Request $request): ?array
    {
        return $request->attributes->get('custom_breadcrumbs');
    }
}

