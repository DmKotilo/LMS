<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Preview;

class LoyaltyReportPage extends Page
{
    public function getTitle(): string
    {
        return 'Отчеты по системе лояльности';
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
        $defaultDateFrom = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $defaultDateTo = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        return [
            Grid::make([
                Column::make([
                    Box::make([
                        Preview::make('Инструкция', 'instruction', fn() => 
                            '<div style="padding: 12px; background-color: #f3f4f6; border-radius: 8px; color: #374151;">
                                Выберите период для генерации отчета по системе лояльности. Отчет будет содержать: количество активных участников, средний размер накопленных бонусов и количество использованных бонусов за выбранный период.
                            </div>'
                        ),
                        FormBuilder::make()
                            ->name('loyalty-report')
                            ->method(FormMethod::GET)
                            ->action(route('moonshine.loyalty.export'))
                            ->fields([
                                Date::make('Дата начала периода', 'date_from')
                                    ->default($defaultDateFrom)
                                    ->required(),
                                Date::make('Дата окончания периода', 'date_to')
                                    ->default($defaultDateTo)
                                    ->required(),
                            ])
                            ->submit('Сгенерировать отчет CSV', [
                                'class' => 'moonshine-btn moonshine-btn-primary',
                            ]),
                    ])
                ], colSpan: 12),
            ])
        ];
    }
}

