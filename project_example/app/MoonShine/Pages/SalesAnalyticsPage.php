<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use Illuminate\Support\Facades\DB;
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
use Order\Models\Order;
use Order\Models\OrderItem;
use Product\Models\Product;

class SalesAnalyticsPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика и отчеты по продажам';
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

        // Общая выручка
        $totalRevenue = (float) (Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('price_final') ?? 0);

        // Количество проданных товаров
        $totalSoldItems = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        })
            ->sum('quantity') ?? 0;

        // Средняя стоимость товара
        $avgItemPrice = $totalSoldItems > 0
            ? (float) round($totalRevenue / $totalSoldItems, 2)
            : 0.0;

        // Топ продаваемых товаров
        $topProducts = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        })
            ->where('item_type', Product::class)
            ->join('products', 'products.id', '=', 'order_items.item_id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.price) as total_revenue'))
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // График продаж по дням
        $salesByDay = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, SUM(price_final) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // График количества проданных товаров по дням
        $itemsByDay = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        })
            ->selectRaw("DATE(created_at) as day, SUM(quantity) as total")
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
                                Выберите период для анализа продаж. После выбора дат страница автоматически обновится.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('sales-analytics-filter')
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
                    ValueMetric::make('Общая выручка за период')
                        ->value(number_format($totalRevenue, 2, '.', ' ') . ' ₽')
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Количество проданных товаров')
                        ->value($totalSoldItems)
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Средняя стоимость товара')
                        ->value(number_format($avgItemPrice, 2, '.', ' ') . ' ₽')
                ], colSpan: 4),

                Column::make([
                    Preview::make('Экспорт данных', 'export_button', function () use ($dateFrom, $dateTo) {
                        $url = route('moonshine.sales.export') . '?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo);
                        return '<a href="' . $url . '" target="_blank" class="moonshine-btn moonshine-btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                            <span>Выгрузить CSV</span>
                        </a>';
                    })
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Выручка по дням')
                        ->line([
                            'Выручка (₽)' => $salesByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Количество проданных товаров по дням')
                        ->line([
                            'Товары' => $itemsByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    Preview::make('Топ продаваемых товаров', 'top_products', function () use ($topProducts) {
                        $html = '<div style="padding: 12px; background-color: #f9fafb; border-radius: 8px;">';
                        $html .= '<table style="width: 100%; border-collapse: collapse;">';
                        $html .= '<thead><tr style="border-bottom: 2px solid #e5e7eb;">';
                        $html .= '<th style="padding: 8px; text-align: left;">Товар</th>';
                        $html .= '<th style="padding: 8px; text-align: right;">Количество</th>';
                        $html .= '<th style="padding: 8px; text-align: right;">Выручка</th>';
                        $html .= '</tr></thead><tbody>';
                        
                        foreach ($topProducts as $product) {
                            $html .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
                            $html .= '<td style="padding: 8px;">' . htmlspecialchars($product->name) . '</td>';
                            $html .= '<td style="padding: 8px; text-align: right;">' . $product->total_quantity . '</td>';
                            $html .= '<td style="padding: 8px; text-align: right;">' . number_format((float) $product->total_revenue, 2, '.', ' ') . ' ₽</td>';
                            $html .= '</tr>';
                        }
                        
                        $html .= '</tbody></table></div>';
                        return $html;
                    })
                ], colSpan: 12),
            ])
        ];
    }
}

