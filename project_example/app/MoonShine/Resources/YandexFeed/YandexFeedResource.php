<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\YandexFeed;

use App\MoonShine\Resources\Product\ProductCategoryResource;
use Carbon\Carbon;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Position;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use YandexFeed\Models\YandexFeed;
use YandexFeed\Services\YmlGeneratorService;


/**
 * @extends ModelResource<YandexFeed>
 */
class YandexFeedResource extends ModelResource
{
    protected string $model = YandexFeed::class;

    protected string $title = 'Яндекс Фиды';

    protected string $column = 'name';

    protected bool $createInModal = false;

    protected bool $editInModal = false;

    protected bool $detailInModal = false;

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $domain = env('APP_URL','https://shop.primefoods.ru');
        $today = Carbon::now()->translatedFormat('d_m_Y-H_i');
        $defaultName = 'feed' . '-' . $today . '.yml';
        return [
            Tabs::make([
                Tabs\Tab::make('Основные настройки',[
                    Box::make([
                        Grid::make([
                            Column::make([
                                Text::make('Название профиля', 'name')
                                    ->default('Выгрузка для маркета')
                                    ->required()
                                    ->hint('Название профиля для идентификации'),
                            ])->columnSpan(6),
                            Column::make([
                                Text::make('Имя файла', 'file_name')
                                    ->default($defaultName)
                                    ->required()
                                    ->hint('Имя файла фида  формате .yml'),
                            ])->columnSpan(6),
                        ]),
                        Grid::make([
                            Column::make([
                                Text::make('Доменное имя', 'domain_name')->default($domain)
                                    ->hint('Доменное имя для ссылок в фиде'),
                            ])->columnSpan(6),
                            Column::make([
                                Text::make('Название компании', 'company_name')->default("PRIME FOODS")
                                    ->hint('Название компании для фида'),
                            ])->columnSpan(6),
                        ]),
                        Switcher::make('Активен', 'is_active')
                            ->default(true),
                    ]),
                ]),
                Tabs\Tab::make('Настройки экспорта',[
                    Box::make([
                        Switcher::make('Выгружать только доступные к покупке товары', 'export_only_available')
                            ->default(true)
                            ->hint('Товары с ценой, активные, не дубляжи, с категориями'),
                        Switcher::make('Выгружать только товары с остатком', 'export_only_with_stock')
                            ->default(false)
                            ->hint('Товары, у которых есть остаток хотя бы в одном городе'),
                        Switcher::make('Использовать HTTPS в ссылках', 'use_https')
                            ->default(true),
                        Switcher::make('Добавлять реферер к ссылкам', 'add_referrer')
                            ->default(false),
                        Select::make('Кодировка', 'encoding')
                            ->options([
                                'utf-8' => 'UTF-8',
                                'windows-1251' => 'Windows-1251',
                            ])
                            ->default('utf-8'),
                        Number::make('Время выполнения шага (секунды)', 'step_time')
                            ->default(30)
                            ->hint('0 - выгрузить все сразу, положительное значение - число секунд на выполнение одного шага'),
                    ]),
                ]),
                Tabs\Tab::make('Категории',[
                    Preview::make('Выберите категории', 'categories', fn () => view('moonshine.components.categories-selected',
                        [
                            'categoriesIdCheckedArray' => $this->getItem() ? ($this->getItem()->selected_categories ?? []) : [],
                            'defaultChecked' => false,
                            'item' => $this->getItem()
                        ]))
                        ->hint('Выберите категории для экспорта. Если ничего не выбрано, будут экспортированы все категории.'),
                ]),
                Tabs\Tab::make('Настройки валют',[
                    Box::make([
                        Json::make('Валюты', 'currencies')
                            ->hint('Список валют для YML-фида. Каждая запись: {\"id\": \"RUB\", \"rate\": \"1\"}.')
                            ->fields([
                                Text::make('Код валюты', 'id')
                                    ->hint('Например: RUB, USD, EUR'),
                                Text::make('Курс', 'rate')
                                    ->hint('Например: 1, 68.79, 78.32'),
                            ])
                            ->removable()
                            ->default(YandexFeed::getDefaultCurrencies()),
                    ]),
                ]),
                Tabs\Tab::make('Тип описания товаров',[
                    Box::make([
                        Select::make('Тип описания', 'offer_type')
                            ->options([
                                'simple' => 'Стандартный',
                                'arbitrary' => 'Произвольный',
                                'combined' => 'Комбинированный (name + произвольные поля)',
                            ])
                            ->default('simple')
                            ->hint('Выберите тип описания товаров в фиде'),
                    ]),
                ]),
                Tabs\Tab::make('Настройки Cron',[
                    Box::make([
                        Grid::make([
                            Column::make([
                                Number::make('Интервал между запусками (часов)', 'cron_interval_hours')
                                    ->hint('Интервал между автоматическими запусками генерации фида'),
                            ])->columnSpan(6),
                            Column::make([
                                Text::make('Время запуска', 'cron_start_time')->setAttribute('type', 'time')
                                    ->hint('Время запуска генерации фида'),
                            ])->columnSpan(6),
                        ]),
                        Text::make('Путь к PHP', 'cron_php_path')
                            ->readonly()
                            ->default('/usr/local/php/bin/php')
                            ->hint('Путь к исполняемому файлу PHP для cron'),
                        Switcher::make('Установить автоматически', 'cron_auto_install')
                            ->default(false)
                            ->hint('Автоматически установить задачу в cron'),
                    ]),
                ]),

            ]),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'name')->sortable(),
            Text::make('Имя файла', 'file_name'),
            Text::make('URL фида', 'feed_url', function($item) {
                return $item instanceof YandexFeed ? $item->getFeedUrl() : '';
            })
                ->link(function($item) {
                    return $item ?? '#';
                }, blank: true),
            Switcher::make('Активен', 'is_active'),

        ];
    }
    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'name')->sortable(),
            Text::make('Имя файла', 'file_name'),
            Text::make('URL фида', 'feed_url', function($item) {
                return $this->getItem()->getFeedUrl() ?? '';
            })->link($this->getItem()->getFeedUrl(), $this->getItem()->getFeedUrl(), blank: true),
            Switcher::make('Активен', 'is_active'),
            Json::make('Валюты', 'currencies')
                ->hint('Список валют для YML-фида. Каждая запись: {\"id\": \"RUB\", \"rate\": \"1\"}.')
                ->fields([
                    Text::make('Код валюты', 'id')
                        ->hint('Например: RUB, USD, EUR'),
                    Text::make('Курс', 'rate')
                        ->hint('Например: 1, 68.79, 78.32'),
                ])
                ->removable()
                ->default(YandexFeed::getDefaultCurrencies()),
            Switcher::make('Выгружать только доступные к покупке товары', 'export_only_available')
                ->default(true),
            Switcher::make('Выгружать только товары с остатком', 'export_only_with_stock')
                ->default(false),
            Switcher::make('Использовать HTTPS в ссылках', 'use_https')
                ->default(true),
            Switcher::make('Добавлять реферер к ссылкам', 'add_referrer')
                ->default(false),
            Select::make('Кодировка', 'encoding')
                ->options([
                    'utf-8' => 'UTF-8',
                    'windows-1251' => 'Windows-1251',
                ])
                ->default('utf-8'),
            Number::make('Время выполнения шага (секунды)', 'step_time')
                ->default(30)
                ->hint('0 - выгрузить все сразу, положительное значение - число секунд на выполнение одного шага'),
        ];
    }

    /**
     * @param YandexFeed $item
     *
     * @return array<string, string[]|string>
     */
    protected function rules(mixed $item): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'file_name' => ['required', 'string', 'max:255'],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'selected_categories' => ['nullable', 'array'],
            'export_only_available' => ['boolean'],
            'export_only_with_stock' => ['boolean'],
            'use_https' => ['boolean'],
            'add_referrer' => ['boolean'],
            'encoding' => ['string', 'in:utf-8,windows-1251'],
            'step_time' => ['integer', 'min:0'],
            'currencies' => ['nullable', 'array'],
            'offer_type' => ['string', 'in:simple,arbitrary,combined'],
            'cron_interval_hours' => ['nullable', 'integer', 'min:0'],
            'cron_start_time' => ['nullable', 'date_format:H:i'],
            'cron_php_path' => ['nullable', 'string'],
            'cron_auto_install' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Действия после сохранения
     */
    protected function afterSave(mixed $item, \MoonShine\Contracts\Core\DependencyInjection\FieldsContract $fields): mixed
    {
        if ($item instanceof YandexFeed) {
            $this->setSelectedCategories($item);

            $generator = new YmlGeneratorService($item);
            $generator->saveToFile();
        }

        return parent::afterSave($item, $fields);
    }

    /**
     * Установить выбранные категории из формы
     */
    protected function setSelectedCategories(YandexFeed $item): void
    {
        $categories = request()->input('categories');

        if (!$categories || !is_array($categories)) {
            $item->selected_categories = [];
            $item->saveQuietly();
            return;
        }


        $selectedCategoryIds = array_map(
            'intval',
            array_keys(array_filter($categories, fn($value) => $value === 'on'))
        );

        $item->selected_categories = array_values($selectedCategoryIds);
        $item->saveQuietly();
    }
}

