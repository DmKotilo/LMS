<?php

namespace YandexFeed\Services;

use Category\Models\Category;
use DOMDocument;
use DOMElement;
use Product\Models\Product;
use YandexFeed\Models\YandexFeed;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YmlGeneratorService
{
    protected YandexFeed $feed;

    public function __construct(YandexFeed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Генерация YML фида
     */
    public function generate(): string
    {
        // Для windows-1251 нужно использовать правильную кодировку
        $encoding = $this->feed->encoding === 'windows-1251' ? 'windows-1251' : 'UTF-8';
        $xml = new DOMDocument('1.0', $encoding);
        $xml->formatOutput = true;

        // Корневой элемент
        $ymlCatalog = $xml->createElement('yml_catalog');
        $ymlCatalog->setAttribute('date', now()->format('Y-m-d H:i'));
        $xml->appendChild($ymlCatalog);

        // Элемент shop
        $shop = $xml->createElement('shop');
        $ymlCatalog->appendChild($shop);

        // Название магазина
        $name = $xml->createElement('name', htmlspecialchars($this->feed->company_name ?? config('app.name')));
        $shop->appendChild($name);

        // Компания
        $company = $xml->createElement('company', htmlspecialchars($this->feed->domain_name ?? parse_url(config('app.url'), PHP_URL_HOST)));
        $shop->appendChild($company);

        // URL магазина
        $url = $xml->createElement('url', $this->getBaseUrl());
        $shop->appendChild($url);

        // Платформа
        $platform = $xml->createElement('platform', 'Laravel');
        $shop->appendChild($platform);

        // Валюты
        $currencies = $xml->createElement('currencies');
        $shop->appendChild($currencies);

        $feedCurrencies = $this->feed->currencies ?? YandexFeed::getDefaultCurrencies();
        foreach ($feedCurrencies as $currency) {
            $currencyEl = $xml->createElement('currency');
            $currencyEl->setAttribute('id', $currency['id']);
            $currencyEl->setAttribute('rate', $currency['rate']);
            $currencies->appendChild($currencyEl);
        }

        // Категории
        $categories = $xml->createElement('categories');
        $shop->appendChild($categories);

        $selectedCategoryIds = $this->feed->selected_categories ?? [];
        $this->addCategories($xml, $categories, $selectedCategoryIds);

        // Товары
        $offers = $xml->createElement('offers');
        $shop->appendChild($offers);

        $this->addOffers($xml, $offers, $selectedCategoryIds);

        return $xml->saveXML();
    }

    /**
     * Добавление категорий в фид
     */
    protected function addCategories(DOMDocument $xml, DOMElement $parent, array $selectedIds): void
    {
        $query = Category::query();

        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        // Получаем все категории
        $allCategories = $query->get();

        // Разделяем на родительские и дочерние
        $parentCategories = $allCategories->whereNull('parent_category_id')->sortBy('id');

        // Группируем дочерние категории по родителю
        $childCategories = $allCategories->whereNotNull('parent_category_id')
            ->groupBy('parent_category_id');

        // Для каждого родителя выводим его и сразу его детей
        foreach ($parentCategories as $parentCategory) {
            // Выводим родительскую категорию
            $categoryEl = $xml->createElement('category', htmlspecialchars($parentCategory->name));
            $categoryEl->setAttribute('id', $parentCategory->id);
            $parent->appendChild($categoryEl);

            // Выводим дочерние категории этого родителя (отсортированные по id)
            if ($childCategories->has($parentCategory->id)) {
                $children = $childCategories->get($parentCategory->id)->sortBy('id');
                foreach ($children as $childCategory) {
                    $childEl = $xml->createElement('category', htmlspecialchars($childCategory->name));
                    $childEl->setAttribute('id', $childCategory->id);
                    $childEl->setAttribute('parentId', $childCategory->parent_category_id);
                    $parent->appendChild($childEl);
                }
            }
        }
    }

    /**
     * Добавление товаров в фид
     */
    protected function addOffers(\DOMDocument $xml, \DOMElement $parent, array $selectedCategoryIds): void
    {
        $query = Product::with(['categories', 'images', 'manufacturer', 'cityCounts']);

        // Если включена опция "только доступные к покупке", используем scopeActive
        // scopeActive проверяет все условия для показа на сайте:
        // - есть цена (price не null)
        // - товар активен (is_active = true)
        // - не является дубляжом (is_analog = false или null)
        // - есть категории (whereHas('categories'))
        if ($this->feed->export_only_available) {
            $query->active();
        } else {
            // Если опция выключена, используем только базовую проверку активности
            $query->where('is_active', true);
        }

        // Фильтрация по остаткам, если включена
        if ($this->feed->export_only_with_stock) {
            $query->whereHas('cityCounts', function ($q) {
                $q->where('quantity', '>', 0);
            });
        }

        // Фильтрация по выбранным категориям
        if (!empty($selectedCategoryIds)) {
            $query->whereHas('categories', function ($q) use ($selectedCategoryIds) {
                $q->whereIn('categories.id', $selectedCategoryIds);
            });
        }

        $products = $query->get();

        foreach ($products as $product) {
            $offer = $this->createOffer($xml, $product);
            if ($offer) {
                $parent->appendChild($offer);
            }
        }
    }

    /**
     * Создание элемента offer для товара
     */
    protected function createOffer(\DOMDocument $xml, Product $product): ?\DOMElement
    {
        $offer = $xml->createElement('offer');
        $offer->setAttribute('id', $product->id);
        $offer->setAttribute('available', $product->is_active ? 'true' : 'false');

        // Добавляем атрибут type="vendor.model" для произвольного и комбинированного типов
        if (in_array($this->feed->offer_type, ['arbitrary', 'combined'])) {
            $offer->setAttribute('type', 'vendor.model');
        }

        // URL товара
        $url = $this->getProductUrl($product);
        $urlEl = $xml->createElement('url', htmlspecialchars($url));
        $offer->appendChild($urlEl);

        // Получаем цены: базовую и со скидкой (если есть акция)
        // Для весовых товаров используется цена за кг, для остальных - обычная цена
        $priceData = $this->getProductPrices($product);
        $price = $priceData['price'];
        $oldPrice = $priceData['oldPrice'];

        // Если есть старая цена (уже проверена на >= 5% в getProductPrices), добавляем oldprice перед price
        if ($oldPrice && $oldPrice > $price) {
            $oldPriceEl = $xml->createElement('oldprice', number_format($oldPrice, 2, '.', ''));
            $offer->appendChild($oldPriceEl);
        }

        $priceEl = $xml->createElement('price', number_format($price, 2, '.', ''));
        $offer->appendChild($priceEl);

        // Валюта
        $currencyId = $xml->createElement('currencyId', 'RUB');
        $offer->appendChild($currencyId);

        // Категория
        $mainCategory = $product->categories->first();
        if ($mainCategory) {
            $categoryId = $xml->createElement('categoryId', $mainCategory->id);
            $offer->appendChild($categoryId);
        }

        // Изображения (YML поддерживает несколько элементов picture)
        $images = $product->images;
        foreach ($images as $image) {
            if ($image && $image->path) {
                $imageUrl = $this->getImageUrl($image->path);
                $picture = $xml->createElement('picture', htmlspecialchars($imageUrl));
                $offer->appendChild($picture);
            }
        }

        // Название товара в зависимости от типа описания
        $this->addName($xml, $offer, $product);

        // Описание
        if ($product->description) {
            $description = $xml->createElement('description', htmlspecialchars(strip_tags($product->description)));
            $offer->appendChild($description);
        }

        return $offer;
    }

    /**
     * Добавление названия товара в зависимости от типа описания
     */
    protected function addName(\DOMDocument $xml, \DOMElement $offer, Product $product): void
    {
        switch ($this->feed->offer_type) {
            case 'arbitrary':
                // Произвольный тип: typePrefix, vendor, model (БЕЗ name)
                $this->addArbitraryFields($xml, $offer, $product);
                break;

            case 'combined':
                // Комбинированный тип: и name, и произвольные поля
                $name = $xml->createElement('name', htmlspecialchars($product->name));
                $offer->appendChild($name);
                $this->addArbitraryFields($xml, $offer, $product);
                break;

            case 'simple':
            default:
                // Упрощенный тип: только name
                $name = $xml->createElement('name', htmlspecialchars($product->name));
                $offer->appendChild($name);
                break;
        }
    }

    /**
     * Добавить поля произвольного типа (typePrefix, vendor, model)
     */
    protected function addArbitraryFields(\DOMDocument $xml, \DOMElement $offer, Product $product): void
    {
        // typePrefix - тип товара (название родительской категории верхнего уровня)
        $typePrefix = $this->getTypePrefix($product);
        if ($typePrefix) {
            $typePrefixEl = $xml->createElement('typePrefix', htmlspecialchars($typePrefix));
            $offer->appendChild($typePrefixEl);
        }

        // vendor - производитель
        if ($product->manufacturer) {
            $vendor = $xml->createElement('vendor', htmlspecialchars($product->manufacturer->name));
            $offer->appendChild($vendor);
        }

        // model - название товара без производителя и типа
        $model = $this->extractModel($product);
        if ($model) {
            $modelEl = $xml->createElement('model', htmlspecialchars($model));
            $offer->appendChild($modelEl);
        }
    }

    /**
     * Получить typePrefix (тип товара) - название родительской категории верхнего уровня
     */
    protected function getTypePrefix(Product $product): ?string
    {
        $mainCategory = $product->categories->first();
        if (!$mainCategory) {
            return null;
        }

        // Находим родительскую категорию верхнего уровня
        $topLevelCategory = $mainCategory;
        while ($topLevelCategory->parent) {
            $topLevelCategory = $topLevelCategory->parent;
        }

        return $topLevelCategory->name;
    }

    /**
     * Извлечь model из названия товара (убрать производителя и тип)
     */
    protected function extractModel(Product $product): string
    {
        $name = $product->name;
        $vendorName = $product->manufacturer?->name;
        $typePrefix = $this->getTypePrefix($product);

        // Убираем производителя из названия
        if ($vendorName) {
            $name = preg_replace('/\b' . preg_quote($vendorName, '/') . '\b/i', '', $name);
        }

        // Убираем тип товара (typePrefix) из названия
        if ($typePrefix) {
            $name = preg_replace('/\b' . preg_quote($typePrefix, '/') . '\b/i', '', $name);
        }

        // Очищаем от лишних пробелов
        $name = preg_replace('/\s+/', ' ', trim($name));

        return $name ?: $product->name; // Если ничего не осталось, возвращаем исходное название
    }

    /**
     * Получить URL товара
     */
    protected function getProductUrl(Product $product): string
    {
        $baseUrl = $this->getBaseUrl();
        $productUrl = '/catalog/product/' . $product->slug;

        $url = $baseUrl . $productUrl;

        if ($this->feed->add_referrer) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'r1=yandext&r2=';
        }

        return $url;
    }

    /**
     * Получить URL изображения
     */
    protected function getImageUrl(string $path): string
    {
        $baseUrl = $this->getBaseUrl();

        // Если путь уже полный URL, вернуть как есть
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        if (!Str::startsWith($normalizedPath, 'storage/')) {
            $normalizedPath = 'storage/' . $normalizedPath;
        }

        return rtrim($baseUrl, '/') . '/' . $normalizedPath;
    }

    /**
     * Получить базовый URL
     */
    protected function getBaseUrl(): string
    {
        $protocol = $this->feed->use_https ? 'https' : 'http';
        $domain = $this->feed->domain_name ?? config('app.url');
        $domain = str_replace(['http://', 'https://'], '', $domain);

        return "{$protocol}://{$domain}";
    }

    /**
     * Получить цены товара для фида (текущая и старая)
     * Для весовых товаров возвращает цену за килограмм (базовую цену)
     * Для остальных товаров возвращает обычную цену
     *
     * @return array{price: float, oldPrice: float|null}
     */
    protected function getProductPrices(Product $product): array
    {
        // Для весовых товаров (руб/кг) используем базовую цену за кг (без умножения на вес)
        // Для остальных товаров используем обычную цену
        $basePrice = $product->price ?? 0;

        if ($basePrice <= 0) {
            return ['price' => 0, 'oldPrice' => null];
        }

        // Ищем активную акцию для товара (без привязки к городу, так как в фиде нет сессии)
        $promotion = $product->promotion()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        // Если есть акция с процентом скидки, рассчитываем цену со скидкой
        if ($promotion && $promotion->sale_percent && $promotion->sale_percent > 0) {
            $salePrice = round($basePrice * (1 - $promotion->sale_percent / 100), 2);

            // Проверяем, что скидка >= 5% (требование Яндекс.Директ)
            $discountPercent = (($basePrice - $salePrice) / $basePrice) * 100;

            if ($discountPercent >= 5 && $salePrice > 0 && $salePrice < $basePrice) {
                // Возвращаем цену со скидкой и старую цену
                return [
                    'price' => $salePrice,
                    'oldPrice' => $basePrice
                ];
            }
        }

        // Если нет акции или скидка < 5%, возвращаем только базовую цену
        return [
            'price' => $basePrice,
            'oldPrice' => null
        ];
    }

    /**
     * Сохранить фид в файл
     */
    public function saveToFile(): bool
    {
        try {
            $xmlContent = $this->generate();
            $filePath = $this->feed->getFeedPath();

            // Создать директорию, если не существует
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($filePath, $xmlContent);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при сохранении YML фида: ' . $e->getMessage());
            return false;
        }
    }
}

