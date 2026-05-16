<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

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
use Order\Models\Order;
use Order\Models\OrderItem;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use Order\Enums\OrderDeliveryTypeEnum;

class OrdersAnalyticsPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика заказов';
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

        // Общее количество заказов за период
        $totalOrders = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Общая сумма заказов
        $totalAmount = (float) (Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('price_final') ?? 0);

        // Средний чек
        $avgOrderAmount = $totalOrders > 0 
            ? round($totalAmount / $totalOrders, 2) 
            : 0;

        // Распределение заказов по статусам
        $ordersByStatus = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(function ($value, $key) {
                $statusEnum = OrderStatusEnum::tryFrom($key);
                $label = $statusEnum ? $statusEnum->toString() : ($key ?: 'Не указан');
                return [$label => $value];
            })
            ->toArray();

        if (empty($ordersByStatus)) {
            $ordersByStatus = ['Нет данных' => 0];
        }

        // Распределение по типам доставки
        $ordersByDeliveryType = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('delivery_type, COUNT(*) as total')
            ->groupBy('delivery_type')
            ->pluck('total', 'delivery_type')
            ->mapWithKeys(function ($value, $key) {
                $deliveryEnum = OrderDeliveryTypeEnum::tryFrom($key);
                $label = $deliveryEnum ? $deliveryEnum->toString() : ($key ?: 'Не указан');
                return [$label => $value];
            })
            ->toArray();

        if (empty($ordersByDeliveryType)) {
            $ordersByDeliveryType = ['Нет данных' => 0];
        }

        // График заказов по дням
        $ordersByDay = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // График суммы заказов по дням
        $amountByDay = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, SUM(price_final) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Количество товаров в заказах
        $totalItems = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        })
            ->sum('quantity') ?? 0;

        return [
            Grid::make([
                Column::make([
                    Box::make([
                        Preview::make('Фильтры', 'filters', fn() => 
                            '<div style="padding: 12px; background-color: #f3f4f6; border-radius: 8px; color: #374151; margin-bottom: 20px;">
                                Выберите период для анализа заказов. После выбора дат страница автоматически обновится.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('orders-analytics-filter')
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
                    ValueMetric::make('Общее количество заказов за период')
                        ->value($totalOrders)
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Общая сумма заказов')
                        ->value(number_format((float) $totalAmount, 2, '.', ' ') . ' ₽')
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Средний чек')
                        ->value(number_format((float) $avgOrderAmount, 2, '.', ' ') . ' ₽')
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Количество товаров в заказах')
                        ->value($totalItems)
                ], colSpan: 3),

                Column::make([
                    Preview::make('Экспорт данных', 'export_button', function () use ($dateFrom, $dateTo) {
                        $url = route('moonshine.orders.export') . '?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo);
                        return '<a href="' . $url . '" target="_blank" class="moonshine-btn moonshine-btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                            <span>Выгрузить CSV</span>
                        </a>';
                    })
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Количество заказов по дням')
                        ->line([
                            'Заказы' => $ordersByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    LineChartMetric::make('Сумма заказов по дням')
                        ->line([
                            'Сумма (₽)' => $amountByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    DonutChartMetric::make('Распределение заказов по статусам')
                        ->values($ordersByStatus)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0'])
                ], colSpan: 6),

                Column::make([
                    DonutChartMetric::make('Распределение заказов по типам доставки')
                        ->values($ordersByDeliveryType)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0'])
                ], colSpan: 6),
            ])
        ];
    }
}

