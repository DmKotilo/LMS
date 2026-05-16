<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\BonusCard;
use Bonus\Models\UserBonusHistory;
use ContactForm\Models\CallContactForm;
use Illuminate\Support\Facades\DB;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\ActionButton;
use Order\Models\Order;
use Product\Models\Product;
use Review\Models\Review;
use User\Models\User;

#[\MoonShine\MenuManager\Attributes\SkipMenu]

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Grid::make([

                Column::make(
                    [
                        LineChartMetric::make('Количество оставленных отзывов за месяц')
                            ->line([
                                'Reviews' => Review::selectRaw("
                        TO_CHAR(DATE_TRUNC('month', created_at), 'FMMonth YYYY') as month_name,
                        COUNT(*) as total_reviews
                    ")
                                    ->whereBetween('created_at', [
                                        now()->subMonths(5)->startOfMonth()->startOfDay(),
                                        now()->endOfMonth()->endOfDay()
                                    ])
                                    ->groupBy('month_name', DB::raw("DATE_TRUNC('month', created_at)"))
                                    ->orderByRaw("DATE_TRUNC('month', created_at)")
                                    ->pluck('total_reviews', 'month_name')
                                    ->toArray(),
                            ])
                            ->withoutSortKeys(),
                    ],
                    colSpan: 12,
                    adaptiveColSpan: 12
                ),

                Column::make([
                    ValueMetric::make('Средний рейтинг товаров')
                        ->value(fn() =>
                            Review::where('item_type', Product::class)
                            ->whereNotNull('rating')
                                ->avg('rating') ?? 0.0
                        )
                        ->valueFormat(fn ($value) => number_format((float) $value, 1))
                ], colSpan: 6),

                Column::make([
                    ValueMetric::make('Пользователей всего')
                        ->value(
                            User::all()->count()
                        )
                ], colSpan: 6),


                Column::make([
                    DonutChartMetric::make('Топ товаров по положительным отзывам')
                        ->values(
                            tap(
                                Review::where('item_type', Product::class)
                                ->where('rating', '>=', 4)
                                    ->join('products', 'products.id', '=', 'reviews.item_id')
                                    ->select('products.name', DB::raw('COUNT(*) as positive_count'))
                                    ->groupBy('products.name')
                                    ->orderByDesc('positive_count')
                                    ->limit(5)
                                    ->pluck('positive_count', 'products.name')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 6),


                Column::make([
                    DonutChartMetric::make('Использование методов оплаты')
                        ->values(
                            tap(
                                Order::whereNotNull('transaction_method_id')
                                    ->join('transaction_methods', 'orders.transaction_method_id', '=', 'transaction_methods.id')
                                    ->select('transaction_methods.name', DB::raw('COUNT(*) as method_count'))
                                    ->groupBy('transaction_methods.name')
                                    ->orderByDesc('method_count')
                                    ->pluck('method_count', 'transaction_methods.name')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 6),


                Column::make(
                    [
                        LineChartMetric::make('Количество брошенных корзин в день')
                            ->line([
                                'Брошенные корзины' => DB::table('user_carts')
                                    ->join('cart_products', 'user_carts.id', '=', 'cart_products.cart_id')
                                    ->selectRaw("
                DATE(cart_products.created_at) as day,
                COUNT(DISTINCT user_carts.id) as abandoned_count
            ")
                                    ->whereExists(function ($query) {
                                        $query->select(DB::raw(1))
                                            ->from('cart_products as cp2')
                                            ->whereRaw('cp2.cart_id = cart_products.cart_id')
                                            ->groupBy('cp2.cart_id')
                                            ->havingRaw("MAX(cp2.created_at) < ?", [now()->subHours(24)]);
                                    })
                                    ->whereBetween('cart_products.created_at', [
                                        now()->subDays(30)->startOfDay(),
                                        now()->endOfDay(),
                                    ])
                                    ->groupBy('day')
                                    ->orderBy('day')
                                    ->pluck('abandoned_count', 'day')
                                    ->toArray(),
                            ])
                            ->withoutSortKeys()
                    ],
                    colSpan: 12,
                    adaptiveColSpan: 12
                ),

                Column::make([
                    ValueMetric::make('Начислено всего бонусов')
                        ->value(
                            (int) round((float) (UserBonusHistory::whereNotNull('type')->where('type',BonusTypeEnum::Accrual)->sum('amount') ?? 0))
                        )
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Списано всего')
                        ->value(
                            UserBonusHistory::whereNotNull('type')->where('type',BonusTypeEnum::Withdrawal)->sum('amount')
                        )
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Потрачено')
                        ->value(
                            UserBonusHistory::where('type',BonusTypeEnum::Withdrawal)
                                ->where('status',BonusStatusEnum::Spent)
                                ->sum('amount')
                        )
                ], colSpan: 2),

                Column::make([
                    ValueMetric::make('Сгорело')
                        ->value(
                            UserBonusHistory::where('type',BonusTypeEnum::Withdrawal)
                                ->where('status',BonusStatusEnum::Expired)
                                ->sum('amount')
                        )
                ], colSpan: 2),

                Column::make([
                    ValueMetric::make('Доступно сейчас')
                        ->value(
                            UserBonusHistory::where('type',BonusTypeEnum::Accrual)
                                ->where('status',BonusStatusEnum::Active)
                                ->where('remaining_amount', '>', 0)
                                ->where('active_date', '<=', now())
                                ->where(function ($query) {
                                    $query->whereNull('expires_at')
                                        ->orWhere('expires_at', '>', now());
                                })
                                ->sum('remaining_amount')
                        )
                ], colSpan: 2),

                // Аналитика по программе лояльности
                Column::make([
                    ValueMetric::make('Активных участников программы лояльности')
                        ->value(
                            BonusCard::where('is_active', true)->count()
                        )
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Средний размер накопленных бонусов')
                        ->value(function () {
                            $avg = UserBonusHistory::where('type', BonusTypeEnum::Accrual)
                                ->where('status', BonusStatusEnum::Active)
                                ->where('remaining_amount', '>', 0)
                                ->where('active_date', '<=', now())
                                ->where(function ($query) {
                                    $query->whereNull('expires_at')
                                        ->orWhere('expires_at', '>', now());
                                })
                                ->groupBy('user_id')
                                ->selectRaw('user_id, SUM(remaining_amount) as total_bonus')
                                ->get()
                                ->avg('total_bonus');
                            
                            return $avg ? number_format((float) $avg, 2) : '0.00';
                        })
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Использовано бонусов за месяц')
                        ->value(
                            UserBonusHistory::where('type', BonusTypeEnum::Withdrawal)
                                ->where('status', BonusStatusEnum::Spent)
                                ->whereBetween('created_at', [
                                    now()->subMonth()->startOfMonth()->startOfDay(),
                                    now()->endOfMonth()->endOfDay()
                                ])
                                ->sum('amount')
                        )
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Использовано бонусов за неделю')
                        ->value(
                            UserBonusHistory::where('type', BonusTypeEnum::Withdrawal)
                                ->where('status', BonusStatusEnum::Spent)
                                ->whereBetween('created_at', [
                                    now()->subWeek()->startOfDay(),
                                    now()->endOfDay()
                                ])
                                ->sum('amount')
                        )
                ], colSpan: 4),
                Column::make(
                    [
                        LineChartMetric::make('Общее количество "Позвонить мне" в неделю ')
                            ->line([
                                'Заявки' => DB::table('call_contact_forms')
                                    ->selectRaw("TO_CHAR(created_at::date, 'DD.MM') as day, COUNT(*) as total")
                                    ->whereBetween('created_at', [
                                        now()->subDays(6)->startOfDay(),
                                        now()->endOfDay()
                                    ])
                                    ->groupBy('day')
                                    ->orderByRaw("MIN(created_at)")
                                    ->pluck('total', 'day')
                                    ->toArray()
                            ])
                            ->withoutSortKeys()
                    ],
                    colSpan: 12,
                    adaptiveColSpan: 12,
                ),
                Column::make([
                    DonutChartMetric::make('Распределение заявок по времени суток')
                        ->values(
                            tap(
                                DB::table('call_contact_forms')
                                    ->selectRaw("
                        CASE
                            WHEN EXTRACT(HOUR FROM created_at) BETWEEN 6 AND 11 THEN 'Утро'
                            WHEN EXTRACT(HOUR FROM created_at) BETWEEN 12 AND 17 THEN 'День'
                            WHEN EXTRACT(HOUR FROM created_at) BETWEEN 18 AND 23 THEN 'Вечер'
                            ELSE 'Ночь'
                        END AS period,
                        COUNT(*) AS total
                    ")
                                    ->groupBy('period')
                                    ->pluck('total', 'period')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 12),


                Column::make([
                    ValueMetric::make('Комментарий указан в % заявок')
                        ->value(function () {
                            $total = CallContactForm::count();

                            if ($total === 0) {
                                return '0 %';
                            }

                            $withComment = CallContactForm::whereNotNull('comment')->where('comment', '!=', '')->count();
                            $percent = round(($withComment / $total) * 100, 1);

                            return "{$percent} %";
                        }),
                ], colSpan: 6),
                Column::make([
                    ValueMetric::make('Комментарий не указан в % заявок')
                        ->value(function () {
                            $total = CallContactForm::count();

                            if ($total === 0) {
                                return '0 %';
                            }

                            $withComment = CallContactForm::whereNull('comment')->count();
                            $percent = round(($withComment / $total) * 100, 1);

                            return "{$percent} %";
                        })
                ], colSpan: 6),



                Column::make([
                    DonutChartMetric::make('Топ продуктов по кликам рекомендаций')
                        ->values(
                            tap(
                                DB::table('analitics')
                                    ->join('products', 'products.id', '=', 'analitics.product_id')
                                    ->select('products.name', DB::raw('SUM(analitics.recomend_click) as total_clicks'))
                                    ->groupBy('products.name')
                                    ->orderByDesc('total_clicks')
                                    ->limit(5)
                                    ->pluck('total_clicks', 'products.name')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 6),


                Column::make([
                    DonutChartMetric::make('Топ товаров по добавлениям в корзину из рекомендаций')
                        ->values(
                            tap(
                                DB::table('analitics')
                                    ->join('products', 'products.id', '=', 'analitics.product_id')
                                    ->select('products.name', DB::raw('SUM(analitics.recomend_to_cart) as total_adds'))
                                    ->groupBy('products.name')
                                    ->orderByDesc('total_adds')
                                    ->limit(5)
                                    ->pluck('total_adds', 'products.name')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 6),


                Column::make([
                    DonutChartMetric::make('Популярные комбинации товаров')
                        ->values(
                            tap(
                                DB::table('cart_products as cp1')
                                    ->join('cart_products as cp2', function ($join) {
                                        $join->on('cp1.cart_id', '=', 'cp2.cart_id')
                                            ->whereRaw('cp1.product_id < cp2.product_id');
                                    })
                                    ->join('products as p1', 'p1.id', '=', 'cp1.product_id')
                                    ->join('products as p2', 'p2.id', '=', 'cp2.product_id')
                                    ->selectRaw("
                        CONCAT(p1.name, ' + ', p2.name) AS combination,
                        COUNT(*) AS total
                    ")
                                    ->groupBy('combination')
                                    ->orderByDesc('total')
                                    ->limit(7)
                                    ->pluck('total', 'combination')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8','#CCC5B0'])
                ], colSpan: 12),

                // Аналитика заказов
                Column::make(
                    [
                        LineChartMetric::make('Количество заказов по месяцам')
                            ->line([
                                'Заказы' => Order::selectRaw("
                                    TO_CHAR(DATE_TRUNC('month', created_at), 'FMMonth YYYY') as month_name,
                                    COUNT(*) as total_orders
                                ")
                                    ->whereBetween('created_at', [
                                        now()->subMonths(5)->startOfMonth()->startOfDay(),
                                        now()->endOfMonth()->endOfDay()
                                    ])
                                    ->groupBy('month_name', DB::raw("DATE_TRUNC('month', created_at)"))
                                    ->orderByRaw("DATE_TRUNC('month', created_at)")
                                    ->pluck('total_orders', 'month_name')
                                    ->toArray(),
                            ])
                            ->withoutSortKeys()
                    ],
                    colSpan: 12,
                    adaptiveColSpan: 12
                ),

            ])
        ];
    }
}
