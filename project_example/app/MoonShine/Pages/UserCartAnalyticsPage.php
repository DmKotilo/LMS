<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use Cart\Models\UserCart;
use Illuminate\Support\Facades\DB;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

class UserCartAnalyticsPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика брошенных корзин';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $now = now();
        $cutoff = $now->copy()->subHours(24); // Брошенные — не изменялись 24+ часа

        // Общее количество корзин
        $totalCarts = UserCart::count();

        return [
            Grid::make([
                Column::make([
                    LineChartMetric::make('Количество брошенных корзин по дням (посл. 30 дней)')
                        ->line([
                            'Брошенные корзины' => UserCart::whereHas('items')
                                ->where('updated_at', '<', $cutoff)
                                ->selectRaw("
                                    DATE(updated_at) as day,
                                    COUNT(*) as count
                                ")
                                ->whereBetween('updated_at', [
                                    now()->subDays(30)->startOfDay(),
                                    now()->endOfDay(),
                                ])
                                ->groupBy('day')
                                ->orderBy('day')
                                ->pluck('count', 'day')
                                ->toArray(),
                        ])
                        ->withoutSortKeys(),
                ], colSpan: 12),

                Column::make([
                    ValueMetric::make('Всего корзин')
                        ->value($totalCarts),
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Всего брошенных корзин')
                        ->value(
                            UserCart::whereHas('items') // корзина не пуста
                            ->where('updated_at', '<', $cutoff) // старше 24 часов
                            ->count()
                        ),
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Процент брошенных корзин')
                        ->value(function () use ($totalCarts, $cutoff) {
                            if ($totalCarts === 0) {
                                return '0%';
                            }

                            $abandonedCount = UserCart::whereHas('items')
                                ->where('updated_at', '<', $cutoff)
                                ->count();

                            $percent = round(($abandonedCount / $totalCarts) * 100, 2);

                            return $percent . '%';
                        }),
                ], colSpan: 4),

                Column::make([
                    DonutChartMetric::make('Брошенные корзины: Гости vs Пользователи')
                        ->values(
                            tap(
                                UserCart::whereHas('items')
                                    ->where('updated_at', '<', $cutoff)
                                    ->selectRaw("
                                        CASE
                                            WHEN user_id IS NULL THEN 'Гости'
                                            ELSE 'Пользователи'
                                        END as type,
                                        COUNT(*) as count
                                    ")
                                    ->groupBy('type')
                                    ->pluck('count', 'type')
                                    ->toArray(),
                                function (&$values) {
                                    if (empty($values)) {
                                        $values = ['Нет данных' => 0];
                                    }
                                }
                            )
                        )
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0']),
                ], colSpan: 12),
            ])
        ];
    }
}
