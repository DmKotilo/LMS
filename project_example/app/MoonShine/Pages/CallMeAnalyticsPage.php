<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

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
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Support\Enums\FormMethod;

class CallMeAnalyticsPage extends Page
{
    public function getTitle(): string
    {
        return 'Аналитика заявок "Позвонить мне"';
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
        $dateFrom = request()->get('date_from', now()->subWeek()->startOfDay()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfDay()->format('Y-m-d'));
        
        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // Общее количество заявок за период
        $totalRequests = CallContactForm::whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Распределение заявок по времени суток
        $requestsByTime = DB::table('call_contact_forms')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("
                CASE
                    WHEN EXTRACT(HOUR FROM created_at) BETWEEN 6 AND 11 THEN 'Утро (6-11)'
                    WHEN EXTRACT(HOUR FROM created_at) BETWEEN 12 AND 17 THEN 'День (12-17)'
                    WHEN EXTRACT(HOUR FROM created_at) BETWEEN 18 AND 23 THEN 'Вечер (18-23)'
                    ELSE 'Ночь (0-5)'
                END AS period,
                COUNT(*) AS total
            ")
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        if (empty($requestsByTime)) {
            $requestsByTime = ['Нет данных' => 0];
        }

        // Частота использования комментариев
        $totalWithComment = CallContactForm::whereBetween('created_at', [$periodStart, $periodEnd])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->count();

        $totalWithoutComment = CallContactForm::whereBetween('created_at', [$periodStart, $periodEnd])
            ->where(function ($query) {
                $query->whereNull('comment')
                    ->orWhere('comment', '=', '');
            })
            ->count();

        $commentPercent = $totalRequests > 0 
            ? round(($totalWithComment / $totalRequests) * 100, 1) 
            : 0;

        // График заявок по дням
        $requestsByDay = CallContactForm::whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
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
                                Выберите период для анализа заявок "Позвонить мне". После выбора дат страница автоматически обновится.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('call-me-analytics-filter')
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
                    ValueMetric::make('Общее количество заявок за период')
                        ->value($totalRequests)
                ], colSpan: 6),

                Column::make([
                    Preview::make('Экспорт данных', 'export_button', function () use ($dateFrom, $dateTo) {
                        $url = route('moonshine.call-me.export') . '?date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo);
                        return '<a href="' . $url . '" target="_blank" class="moonshine-btn moonshine-btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                            <span>Выгрузить CSV</span>
                        </a>';
                    })
                ], colSpan: 6),

                Column::make([
                    LineChartMetric::make('Количество заявок по дням')
                        ->line([
                            'Заявки' => $requestsByDay
                        ])
                        ->withoutSortKeys()
                ], colSpan: 12),

                Column::make([
                    DonutChartMetric::make('Распределение заявок по времени суток')
                        ->values($requestsByTime)
                        ->colors(['#A39874', '#E4E1D8', '#CCC5B0', '#8B7A5A'])
                ], colSpan: 6),

                Column::make([
                    ValueMetric::make('Комментарий указан в % заявок')
                        ->value($commentPercent . ' %')
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Заявок с комментарием')
                        ->value($totalWithComment)
                ], colSpan: 3),

                Column::make([
                    ValueMetric::make('Заявок без комментария')
                        ->value($totalWithoutComment)
                ], colSpan: 3),
            ])
        ];
    }
}

