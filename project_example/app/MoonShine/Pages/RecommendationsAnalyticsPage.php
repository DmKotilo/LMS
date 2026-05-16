<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use Analitic\Models\Analitic;
use Illuminate\Support\Facades\DB;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Support\Enums\FormMethod;

class RecommendationsAnalyticsPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика блока рекомендаций';
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $dateFrom = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));
        
        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // Общее количество кликов по рекомендованным продуктам
        $totalClicks = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('recomend_click') ?? 0;

        // Общее количество добавлений в корзину из блока рекомендаций
        $totalAddsToCart = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('recomend_to_cart') ?? 0;

        // Топ продуктов по кликам
        $topProductsByClicks = DB::table('analitics')
            ->join('products', 'products.id', '=', 'analitics.product_id')
            ->whereBetween('analitics.created_at', [$periodStart, $periodEnd])
            ->select('products.name', DB::raw('SUM(analitics.recomend_click) as total_clicks'))
            ->groupBy('products.name')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->pluck('total_clicks', 'products.name')
            ->toArray();

        if (empty($topProductsByClicks)) {
            $topProductsByClicks = ['Нет данных' => 0];
        }

        // Топ продуктов по добавлениям в корзину
        $topProductsByCart = DB::table('analitics')
            ->join('products', 'products.id', '=', 'analitics.product_id')
            ->whereBetween('analitics.created_at', [$periodStart, $periodEnd])
            ->select('products.name', DB::raw('SUM(analitics.recomend_to_cart) as total_adds'))
            ->groupBy('products.name')
            ->orderByDesc('total_adds')
            ->limit(10)
            ->pluck('total_adds', 'products.name')
            ->toArray();

        if (empty($topProductsByCart)) {
            $topProductsByCart = ['Нет данных' => 0];
        }

        // Популярные комбинации товаров
        $popularCombinations = DB::table('cart_products as cp1')
            ->join('cart_products as cp2', function ($join) {
                $join->on('cp1.cart_id', '=', 'cp2.cart_id')
                    ->whereRaw('cp1.product_id < cp2.product_id');
            })
            ->join('products as p1', 'p1.id', '=', 'cp1.product_id')
            ->join('products as p2', 'p2.id', '=', 'cp2.product_id')
            ->whereBetween('cp1.created_at', [$periodStart, $periodEnd])
            ->selectRaw("
                CONCAT(p1.name, ' + ', p2.name) AS combination,
                COUNT(*) AS total
            ")
            ->groupBy('combination')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'combination')
            ->toArray();

        if (empty($popularCombinations)) {
            $popularCombinations = ['Нет данных' => 0];
        }

        // График кликов по дням
        $clicksByDay = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, SUM(recomend_click) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // График добавлений в корзину по дням
        $cartAddsByDay = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, SUM(recomend_to_cart) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return [
            Grid::make([
                Column::make([
                    Box::make([
                        Preview::make('Фильтры', 'filters', fn() => 
                            '<div style="padding: 12px; background-color: #f3f4f6; border-radius: 8px; color: #374151; margin-bottom: 20px;">
                                Выберите период для анализа блока рекомендаций. После выбора дат страница автоматически обновится.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('recommendations-analytics-filter')
                            ->method(FormMethod::GET)
                            ->action(request()->url())
                            ->fields([
                                Date::make('Дата начала', 'date_from')
                                    ->default($dateFrom)
                                    ->required(),
                                Date::make('Дата окончания', 'date_to')
                                    ->default($dateTo)
                                    ->required(),
                            ])
                            ->submit('Применить фильтр', [
                                'class' => 'moonshine-btn moonshine-btn-primary',
                            ]),
                    ])
                ], colSpan: 12),

                Column::make([
                    ValueMetric::make('Количество кликов по рекомендованным продуктам')
                        ->value($totalClicks)
                ], colSpan: 6),

                Column::make([
                    ValueMetric::make('Количество добавлений в корзину из блока рекомендаций')
                        ->value($totalAddsToCart)
                ], colSpan: 6),

                Column::make([
                    Preview::make('Экспорт данных', 'export_button', function () use ($dateFrom, $dateTo) {
                        $url = route('moonshine.recommendations.export') . '?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo);
                        return '<a href="' . $url . '" target="_blank" class="moonshine-btn moonshine-btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                            <span>Выгрузить CSV</span>
                        </a>';
                    })
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Клики по рекомендованным продуктам по дням')
                        ->line([
                            'Клики' => $clicksByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Добавления в корзину из рекомендаций по дням')
                        ->line([
                            'Добавления' => $cartAddsByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    DonutChartMetric::make('Топ продуктов по кликам рекомендаций')
                        ->values($topProductsByClicks)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0'])
                ], colSpan: 6),

                Column::make([
                    DonutChartMetric::make('Топ товаров по добавлениям в корзину из рекомендаций')
                        ->values($topProductsByCart)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0'])
                ], colSpan: 6),

                Column::make([
                    DonutChartMetric::make('Популярные комбинации товаров')
                        ->values($popularCombinations)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0', '#C4B99A', '#B4A88A'])
                ], colSpan: 12),
            ])
        ];
    }
}

