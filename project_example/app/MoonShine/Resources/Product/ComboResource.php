<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product;

use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Promotion\PromotionResource;
use Combo\Models\Combo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\AssetManager\InlineJs;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Product\Models\Product;

/**
 * @extends ModelResource<Combo>
 */
class ComboResource extends ModelResource
{
    protected string $model = Combo::class;

    protected string $title = 'Комбо-наборы';

    protected function search(): array
    {
        return [
            'id',
            'name',
            'article_number',
            'description',
            'short_description',
            'seo_title',
            'seo_description',
            'keywords',
            'uuid_1c',
            'name_1c',
        ];
    }

    protected function onLoad(): void
    {
        $this->getAssetManager()
            ->prepend(InlineJs::make(<<<'JS'
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('input[type="file"][name="images[]"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const maxSize = 10 * 1024 * 1024;
                        for (let i = 0; i < this.files.length; i++) {
                            if (this.files[i].size > maxSize) {
                                alert('Максимальный размер загружаемого файла 10МБ');
                                this.value = '';
                                break;
                            }
                        }
                    });
                });
            });
            JS
            ));
    }


    protected array $withCount = ['products'];

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображение','firstImage')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 100px; height: 100px;'
            ]),
            Text::make('Название','name'),
            Number::make('Цена','price'),
            Text::make('Артикул','article_number'),
            Preview::make('Статус', 'products_status', fn($item) =>
                ($item->products_count ?? $item->products()->count()) === 0
                    ? '<span style="color: #dc2626; font-weight: bold;">⚠️ Нет товаров (не отображается на сайте)</span>'
                    : '<span style="color: #16a34a;">✓ Товаров: ' . ($item->products_count ?? $item->products()->count()) . '</span>'
            ),

            Switcher::make('На главной', 'show_main')
                ->updateOnPreview()
                ->onValue(1)
                ->offValue(0)
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $isEditPage = $this->getItem() != null;
        $modelClass = $this->getModel()::class;
        $model = $this->getModel();

        // Загружаем существующие товары с pivot ценами для установки значений по умолчанию
        $productPivotPrices = collect();
        if ($isEditPage && $this->getItem()) {
            $this->getItem()->loadMissing('products');
            $productPivotPrices = $this->getItem()
                ->products
                ->mapWithKeys(fn ($product) => [
                    $product->id => $product->pivot->price ?? null
                ]);
        }

        if ($isEditPage){
            $localizations = Localization::where('localizationable_type', $this->getModel()::class)
                ->where('localizationable_id', $this->getItem()->id)
                ->get()
                ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
        }

        return [
            Box::make([
                Tabs::make([
                    Tab::make('Основное', [
                        ID::make(),
                        Text::make('Название','name')->required(),
                        Text::make('Артикул','article_number')->nullable(),
                        Number::make('Количество','quantity')->required(),
                        Flex::make([
                            Enum::make('Тип веса','weight_type')->attach(WeightTypeEnum::class)->required(),
                            Number::make('Вес','weight')
                                ->buttons()
                                ->step(0.1)
                                ->required(),
                        ]),
                        Flex::make([
                            Enum::make('Тип цены','price_type')->attach(PriceTypeEnum::class)->required(),
                            Number::make('Цена','price')
                                ->step(0.1)
                                ->required()
                                ->disabled(fn() => $isEditPage)
                                ->when($isEditPage, fn($field) => $field->hint('Цена блокирована для редактирования. Автоматически рассчитывается на основе товаров в комбонаборе.')),
                            Preview::make('Цена c учетом акции', 'sale_price_preview', fn() =>
                                '<input type="text" class="form-input" value="' . ($this->getItem()->sale_price ?? null) . ' ₽" readonly disabled style="background-color: #f3f4f6; cursor: not-allowed;">'
                            )
                            ->canSee(fn() => $isEditPage && $this->getItem()->hasActivePromotion())
                            ->hint('Цена с учетом действующей акции (только для просмотра)'),
                        ]),
                    ]),

                    Tab::make('Изображения', [
                        ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ])->reorderable(fn($ctx) =>
                            '/file/sort/reorder-images/' . base64_encode($modelClass) . '/' . $ctx->getData()->getKey()
                        ),
                    ]),

                    Tab::make('Описание', [
                        TinyMce::make('Описание','description')->required(),
                        Text::make('Короткое описание','short_description')->nullable(),
                    ]),

//                    Tab::make('Продукты', [
//                        BelongsToMany::make('Продукты','products','name',ProductResource::class)->selectMode(),
//                    ]),
                    Tab::make('Продукты', [
                        BelongsToMany::make('Продукты', 'products', 'name', ProductResource::class)

                            ->searchable()
                            ->placeholder('Начните вводить название')

                            // Первоначальная выборка — первые 20 товаров
                            ->valuesQuery(fn($query) => $query->where('is_active', true)->orderBy('id')->limit(20))

                            // Асинхронный поиск по имени или ID
                            ->asyncSearch(
                                'name',
                                searchQuery: function ($query, $request, string $term, $field) {
                                    return $query
                                        ->where('is_active', true)
                                        ->where(function ($q) use ($term) {
                                            $q->where('name', 'ilike', "%{$term}%")
                                              ->orWhere('id', (int) $term);
                                        })
                                        ->orderBy('id')
                                        ->limit(20);
                                },
                                limit: 20
                            )
                            ->fields([
                                Number::make('Переопределенная цена', 'price')
                                    ->step(0.1)
                                    ->default(0)
                                    ->hint('Цена товара в комбонаборе (можно изменить)')
                            ]),
                        Preview::make('', 'price_auto_fill_script', function() {
                            $url = route('combo.product-price');
                            return <<<HTML
<script>
(function() {
    'use strict';

    console.log('[Combo Price AutoFill] ════════════════════════════════════════');
    console.log('[Combo Price AutoFill] СКРИПТ ЗАГРУЖЕН!');
    console.log('[Combo Price AutoFill] Document readyState:', document.readyState);
    console.log('[Combo Price AutoFill] ════════════════════════════════════════');

    function initPriceAutoFill() {
        console.log('[Combo Price AutoFill] ════════════════════════════════════════');
        console.log('[Combo Price AutoFill] Инициализация автозаполнения цен');
        console.log('[Combo Price AutoFill] ════════════════════════════════════════');

        const apiUrl = '{$url}';
        console.log('[Combo Price AutoFill] API URL:', apiUrl);

        // Функция для загрузки и установки цены товара
        function fillPriceForProduct(productId, priceInput) {
            if (!productId || !priceInput) {
                console.log('[Combo Price AutoFill] fillPriceForProduct: пропущено (нет productId или priceInput)', {
                    productId: productId,
                    priceInput: priceInput ? 'есть' : 'нет'
                });
                return;
            }

            console.log('[Combo Price AutoFill] Загрузка цены для товара ID:', productId);
            const requestUrl = apiUrl + '?product_id=' + productId;
            console.log('[Combo Price AutoFill] URL запроса:', requestUrl);

            // Загружаем базовую цену товара с сервера
            fetch(requestUrl)
                .then(function(response) {
                    console.log('[Combo Price AutoFill] Ответ получен:', response.status, response.statusText);
                    return response.json();
                })
                .then(function(data) {
                    console.log('[Combo Price AutoFill] Данные от сервера:', data);
                    console.log('[Combo Price AutoFill] Цена товара ID ' + productId + ':', data.price);

                    if (data.price !== undefined) {
                        console.log('[Combo Price AutoFill] Устанавливаем цену ' + data.price + ' для товара ID ' + productId);
                        console.log('[Combo Price AutoFill] Поле цены до установки:', priceInput.name, 'значение:', priceInput.value);

                        // Устанавливаем цену
                        priceInput.value = data.price;

                        console.log('[Combo Price AutoFill] Поле цены после установки:', priceInput.name, 'значение:', priceInput.value);

                        // Триггерим события для уведомления MoonShine об изменении
                        priceInput.dispatchEvent(new Event('change', { bubbles: true }));
                        priceInput.dispatchEvent(new Event('input', { bubbles: true }));

                        console.log('[Combo Price AutoFill] ✓ Цена успешно установлена для товара ID ' + productId);
                    } else {
                        console.warn('[Combo Price AutoFill] ⚠ В ответе сервера нет поля price:', data);
                    }
                })
                .catch(function(error) {
                    console.error('[Combo Price AutoFill]削 ✗ Ошибка загрузки цены товара ID ' + productId + ':', error);
                });
        }

        console.log('[Combo Price AutoFill] Регистрация обработчика события table_row_added:belongs_to_many_products');

        // Обработчик события добавления новой строки в таблицу
        window.addEventListener('table_row_added:belongs_to_many_products', function(event) {
            console.log('[Combo Price AutoFill] ════════════════════════════════════════');
            console.log('[Combo Price AutoFill] Событие table_row_added получено!', event);
            console.log('[Combo Price AutoFill] ════════════════════════════════════════');

            // Ждем немного, чтобы MoonShine успел создать DOM элементы и проставить checkbox
            setTimeout(function() {
                console.log('[Combo Price AutoFill] Обработка события table_row_added...');
                // Находим таблицу с товарами
                const table = document.querySelector('table[data-name="belongs_to_many_products"]');
                console.log('[Combo Price AutoFill] Таблица найдена:', table ? 'да' : 'нет');

                if (!table) {
                    console.warn('[Combo Price AutoFill] Таблица не найдена');
                    return;
                }

                // Находим все строки в таблице
                const rows = table.querySelectorAll('tbody tr');
                console.log('[Combo Price AutoFill] Найдено строк в таблице:', rows.length);

                if (rows.length === 0) {
                    console.warn('[Combo Price AutoFill] Строки в таблице не найдены');
                    return;
                }

                // Берем последнюю строку (новую)
                const lastRow = rows[rows.length - 1];
                console.log('[Combo Price AutoFill] Работаем с последней строкой');

                // Ищем checkbox товара в этой строке
                const checkbox = lastRow.querySelector('input[type="checkbox"][name^="products["]');
                console.log('[Combo Price AutoFill] Checkbox найден:', checkbox ? 'да' : 'нет');

                if (!checkbox) {
                    console.warn('[Combo Price AutoFill] Checkbox не найден в последней строке');
                    return;
                }

                const productId = checkbox.value;
                console.log('[Combo Price AutoFill] ID товара из checkbox:', productId);
                console.log('[Combo Price AutoFill] Checkbox checked:', checkbox.checked);

                if (!productId) {
                    console.warn('[Combo Price AutoFill] ID товара пустой');
                    return;
                }

                // В MoonShine checkbox автоматически становится checked после добавления
                // Ищем поле цены для этого товара
                const priceInputName = 'products_pivot[' + productId + '][pivot][price]';
                console.log('[Combo Price AutoFill] Ищем поле цены с именем:', priceInputName);

                let priceInput = lastRow.querySelector('input[name="' + priceInputName + '"]');
                console.log('[Combo Price AutoFill] Поле найдено в строке:', priceInput ? 'да' : 'нет');

                // Если поле еще не создано в строке, ищем в документе
                if (!priceInput) {
                    console.log('[Combo Price AutoFill] Поле не найдено в строке, ищем по всему документу...');
                    priceInput = document.querySelector('input[name="' + priceInputName + '"]');
                    console.log('[Combo Price AutoFill] Поле найдено в документе:', priceInput ? 'да' : 'нет');
                }

                // Если поле найдено - загружаем и заполняем цену
                if (priceInput) {
                    console.log('[Combo Price AutoFill] Поле цены найдено, текущее значение:', priceInput.value);
                    fillPriceForProduct(productId, priceInput);
                } else {
                    console.warn('[Combo Price AutoFill] Поле цены не найдено, повтор через 100ms...');
                    // Если поле еще не создано, ждем еще немного
                    setTimeout(function() {
                        console.log('[Combo Price AutoFill] Повторный поиск поля цены...');
                        priceInput = document.querySelector('input[name="' + priceInputName + '"]');
                        if (priceInput) {
                            console.log('[Combo Price AutoFill] Поле найдено при повторном поиске, значение:', priceInput.value);
                            fillPriceForProduct(productId, priceInput);
                        } else {
                            console.error('[Combo Price AutoFill] Поле цены так и не найдено');
                        }
                    }, 100);
                }
            }, 100);
        });

        // Обработчик изменения checkbox'ов (если пользователь вручную выбирает/снимает товар)
        document.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'checkbox' && e.target.name && e.target.name.indexOf('products[') === 0) {
                const checkbox = e.target;
                if (checkbox.checked) {
                    const productId = checkbox.value;
                    if (productId) {
                        const priceInputName = 'products_pivot[' + productId + '][pivot][price]';
                        const priceInput = document.querySelector('input[name="' + priceInputName + '"]');
                        // Загружаем цену только если поле пустое или равно 0
                        // Это предотвращает перезапись уже введенной пользователем цены
                        if (priceInput && (!priceInput.value || priceInput.value === '0')) {
                            fillPriceForProduct(productId, priceInput);
                        }
                    }
                }
            }
        }, true);

        console.log('[Combo Price AutoFill] Инициализация завершена');
        console.log('[Combo Price AutoFill] ════════════════════════════════════════');
    }

    // Запускаем после загрузки DOM
    if (document.readyState === 'loading') {
        console.log('[Combo Price AutoFill] DOM загружается, ждем DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[Combo Price AutoFill] DOMContentLoaded сработал');
            initPriceAutoFill();
        });
    } else {
        console.log('[Combo Price AutoFill] DOM уже загружен, запускаем сразу');
        initPriceAutoFill();
    }
})();
</script>
HTML;
                        })->canSee(fn() => false), // Скрываем Preview поле, только скрипт нужен
                    ]),



                    Tab::make('Акции', [
                        BelongsToMany::make('Промоакции','promotion','name', resource: PromotionResource::class)
                            ->selectMode()
                            ->customAttributes([
                                'data-max-item-count' => 1
                            ]),
                        Preview::make('Цена со скидкой', 'sale_price_preview', fn() =>
                            '<input type="text" class="form-input" value="' . ($this->getItem()->sale_price ?? null) . ' ₽" readonly disabled style="background-color: #f3f4f6; cursor: not-allowed;">'
                        )->canSee(fn() => $isEditPage && $this->getItem()->hasActivePromotion())
                        ->hint('Цена с учетом действующей акции (только для просмотра)'),

                        Preview::make('Процент скидки', 'discount_percent', fn() =>
                            '<input type="text" class="form-input" value="' . $this->getItem()->getSalePercent() . '%">'
                        )->canSee(fn() => $isEditPage && $this->getItem()->hasActivePromotion()),

                    ]),

                    Tab::make('Seo', [
                        Text::make('Title','seo_title')->nullable(),
                        Text::make('Description','seo_description')->nullable(),

                        Text::make('Ключевые слова', 'keywords')
                            ->tags(10)
                    ]),

                    Tab::make('1C', [
                        Text::make('uuid_1c','uuid_1c')->nullable(),
                        Text::make('Название_1c','name_1c')->nullable(),

                    ]),

                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => Combo::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),
                ]),

            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 100px; height: 100px;'
            ]),
            Text::make('Название','name'),
            Text::make('Название_1c','name_1c'),
            Text::make('Артикул','article_number'),
            Text::make('uuid_1c','uuid_1c'),
            Number::make('Количество','quantity'),
            Enum::make('Тип веса','weight_type')->attach(WeightTypeEnum::class),
            Number::make('Вес','weight'),
            Enum::make('Тип цены','price_type')->attach(PriceTypeEnum::class),
            Number::make('Цена','price'),
            TinyMce::make('Описание','description'),
            Text::make('Короткое описание','short_description'),
//            Number::make('Скидочная цена','sale_price')->step(0.1),
            Text::make('seo_title'),
            Text::make('seo_description'),
            BelongsToMany::make('Продукты','products','name',ProductResource::class)->selectMode(),
            BelongsToMany::make('Акция набора','promotion','name',resource: PromotionResource::class)


        ];
    }

    /**
     * @param Combo $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
    protected function beforeUpdating(mixed $item): mixed
    {
        // Обработка переводов
        $translations = request('translations', []);
        $modelClass = $item::class;
        $modelId = $item->id;

        foreach ($translations as $langId => $fields) {
            foreach ($fields as $field => $translate) {
                Localization::updateOrCreate(
                    [
                        'localizationable_type' => $modelClass,
                        'localizationable_id' => $modelId,
                        'lang_id' => $langId,
                        'field' => $field,
                    ],
                    [
                        'translate' => $translate ?? '',
                    ]
                );
            }
        }

        // Обработка pivot полей для products
        $this->syncProductsWithPivot($item);

        return $item;
    }

    /**
     * Синхронизирует товары с pivot данными (ценами)
     */
    protected function syncProductsWithPivot(Combo $combo): void
    {
        // MoonShine передает данные в формате products_pivot
        $productsData = request('products', []);
        $pivotData = request('products_pivot', []);

        // Получаем список выбранных товаров (те, у которых галочки стоят)
        $selectedProductIds = is_array($productsData) ? array_keys($productsData) : [];

        if (empty($selectedProductIds)) {
            // Если ничего не выбрано, отвязываем все товары
            $combo->products()->detach();
            return;
        }

        // Получаем текущие связи комбонабора с товарами для проверки, какие товары новые
        $existingProducts = $combo->products()->pluck('products.id')->toArray();

        $syncData = [];

        // Собираем pivot данные только для выбранных товаров
        if (!empty($pivotData)) {
            foreach ($pivotData as $productId => $pivotInfo) {
                // Добавляем только если товар выбран
                if (in_array((string) $productId, $selectedProductIds)) {
                    $price = $pivotInfo['pivot']['price'] ?? null;

                    if ($price !== null && $price !== '') {
                        // Если цена указана, сохраняем её
                        $syncData[$productId] = ['price' => (float) $price];
                    } else {
                        // Если цена пустая - всегда ставим 0
                        // Пользователь должен явно указать цену или она будет 0
                        $syncData[$productId] = ['price' => 0];
                    }
                }
            }
        }

        // Добавляем выбранные товары без указанных цен в pivot
        // Если товар новый и цена не указана - ставим 0
        foreach ($selectedProductIds as $productId) {
            if (!isset($syncData[$productId])) {
                // Товар новый без указанной цены - ставим 0
                $syncData[$productId] = ['price' => 0];
            }
        }

        // Синхронизируем только выбранные товары
        $combo->products()->sync($syncData);
    }

    protected function afterSave(mixed $item, $fields): mixed
    {
        // Синхронизируем промоакции (MoonShine иногда не синхронизирует BelongsToMany)
        $this->syncPromotions($item);

        // Автоматически обновляем цену комбонабора после сохранения связей
        $this->updateComboPriceIfNeeded($item);

        return $item;
    }

    /**
     * Синхронизирует привязанные промоакции
     */
    protected function syncPromotions(Combo $combo): void
    {
        $raw = request('promotion', []);

        // MoonShine может передавать как [id => true], так и [0 => id]
        $selectedIds = collect(is_array($raw) ? $raw : [])
            ->map(fn($value, $key) => is_numeric($key) ? $value : $key)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $combo->promotion()->sync($selectedIds);
    }

    /**
     * Обновляет цену комбонабора на основе суммы переопределенных цен товаров
     */
    protected function updateComboPriceIfNeeded(Combo $combo): void
    {
        // Перезагружаем комбо с актуальными данными из БД (включая pivot)
        $combo->refresh();
        $combo->load('products');

        if ($combo->products->count() === 0) {
            // Если товаров нет, ставим цену 0
            $combo->updateQuietly(['price' => 0]);
            return;
        }

        // Рассчитываем цену только из переопределенных цен товаров (pivot.price)
        // Даже если цена равна 0, она все равно учитывается в сумме
        $totalPrice = 0;

        foreach ($combo->products as $product) {
            $customPrice = $product->pivot->price ?? 0;
            $totalPrice += (float) $customPrice;
        }

        // Всегда обновляем цену комбонабора (даже если сумма = 0)
        $combo->updateQuietly(['price' => round($totalPrice, 2)]);
    }

    /**
     * API метод для получения базовой цены товара
     */
    public function getProductPrice(Request $request)
    {
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $product = \Product\Models\Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $price = $product->getRawOriginal('price') ?? $product->attributes['price'] ?? 0;

        return response()->json(['price' => (float) $price]);
    }

}
