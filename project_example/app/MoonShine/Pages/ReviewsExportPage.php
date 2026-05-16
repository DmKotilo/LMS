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
use Product\Models\Product;
use Review\Models\Review;

class ReviewsExportPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика по отзывам и рейтингам';
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

        // Количество отзывов за период
        $reviewsCount = Review::where('item_type', Product::class)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Средний рейтинг за период
        $avgRating = Review::where('item_type', Product::class)
            ->whereNotNull('rating')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->avg('rating') ?? 0.0;

        // График отзывов по дням
        $reviewsByDay = Review::where('item_type', Product::class)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Топ товаров по положительным отзывам
        $topProductsPositive = Review::where('reviews.item_type', Product::class)
            ->where('reviews.rating', '>=', 4)
            ->whereBetween('reviews.created_at', [$periodStart, $periodEnd])
            ->join('products', 'products.id', '=', 'reviews.item_id')
            ->select('products.name', DB::raw('COUNT(*) as positive_count'))
            ->groupBy('products.name')
            ->orderByDesc('positive_count')
            ->limit(10)
            ->pluck('positive_count', 'products.name')
            ->toArray();

        if (empty($topProductsPositive)) {
            $topProductsPositive = ['Нет данных' => 0];
        }

        // Самые популярные товары по количеству отзывов (всех)
        $topProductsByReviews = Review::where('reviews.item_type', Product::class)
            ->whereBetween('reviews.created_at', [$periodStart, $periodEnd])
            ->join('products', 'products.id', '=', 'reviews.item_id')
            ->select('products.name', DB::raw('COUNT(*) as reviews_count'))
            ->groupBy('products.name')
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->pluck('reviews_count', 'products.name')
            ->toArray();

        if (empty($topProductsByReviews)) {
            $topProductsByReviews = ['Нет данных' => 0];
        }

        return [
            Grid::make([
                Column::make([
                    Box::make([
                        Preview::make('Фильтры', 'filters', fn() => 
                            '<div style="padding: 12px; background-color: #f3f4f6; border-radius: 8px; color: #374151; margin-bottom: 20px;">
                                Выберите период для анализа отзывов и рейтингов. После выбора дат страница автоматически обновится.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('reviews-analytics-filter')
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
                    ValueMetric::make('Количество отзывов за период')
                        ->value($reviewsCount)
                ], colSpan: 4),

                Column::make([
                    ValueMetric::make('Средний рейтинг товаров')
                        ->value(number_format((float) $avgRating, 1))
                ], colSpan: 4),

                Column::make([
                    Preview::make('Экспорт данных', 'export_button', function () use ($dateFrom, $dateTo) {
                        $url = route('moonshine.reviews.export') . '?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo);
                        return '<a href="' . $url . '" target="_blank" class="moonshine-btn moonshine-btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                            <span>Выгрузить CSV</span>
                        </a>';
                    })
                ], colSpan: 4),

                Column::make([
                    LineChartMetric::make('Количество отзывов по дням')
                        ->line([
                            'Отзывы' => $reviewsByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    DonutChartMetric::make('Самые популярные товары по количеству отзывов')
                        ->values($topProductsByReviews)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0'])
                ], colSpan: 6),

                Column::make([
                    DonutChartMetric::make('Топ товаров по количеству положительных отзывов (рейтинг ≥ 4)')
                        ->values($topProductsPositive)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A', '#D4C9B0'])
                ], colSpan: 6),
            ])
        ];
    }
}

