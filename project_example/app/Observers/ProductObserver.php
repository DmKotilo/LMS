<?php

namespace App\Observers;

use App\Services\SitemapService;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Product\Models\Product;

class ProductObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Product "saved" event.
     * price_per_kg пересчитывается при каждом сохранении, если тип цены "руб/кг" —
     * в т.ч. при обновлении из 1С (prices), при создании и любом изменении в админке.
     */
    public function saved(Product $product): void
    {
        if ($product->shouldRecalculatePricePerKg()) {
            $this->recalculatePricePerKg($product);
        }

        try {
            $shouldInclude = $this->sitemapService->shouldIncludeModel('product', $product);
            $this->sitemapService->updateItem('product', $product, $shouldInclude);
        } catch (\Throwable $e) {
            Log::warning('Sitemap: ошибка при обновлении карты сайта после сохранения продукта (продукт сохранён)', [
                'product_id' => $product->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Пересчитать price_per_kg для продукта и всех связанных city_product_prices
     */
    protected function recalculatePricePerKg(Product $product): void
    {
        if (! $product->shouldRecalculatePricePerKg()) {
            return;
        }

        $pricePerKg = null;
        
        // Если тип цены "руб/кг", рассчитываем цену
        if ($product->price && $product->weight) {
            // Получаем вес в килограммах (конвертируем граммы в кг если нужно)
            $weightInKilos = $this->getWeightInKilos($product);
            $pricePerKg = round($product->price * $weightInKilos, 2);
        }

        // Обновляем price_per_kg у продукта без событий, чтобы избежать рекурсии
        DB::table('products')
            ->where('id', $product->id)
            ->update(['price_per_kg' => $pricePerKg]);

        $cityPricesCount = 0;
        // Пересчитываем price_per_kg для всех связанных city_product_prices
        if ($product->weight) {
            // Получаем вес в килограммах
            $weightInKilos = $this->getWeightInKilos($product);
            
            // Получаем все цены по городам для этого продукта
            $cityPrices = DB::table('city_product_prices')
                ->where('product_id', $product->id)
                ->get();
            $cityPricesCount = $cityPrices->count();

            foreach ($cityPrices as $cityPrice) {
                $cityPricePerKg = null;
                if ($cityPrice->price) {
                    $cityPricePerKg = round($cityPrice->price * $weightInKilos, 2);
                }

                DB::table('city_product_prices')
                    ->where('id', $cityPrice->id)
                    ->update(['price_per_kg' => $cityPricePerKg]);
            }
        }

        Log::info('price_per_kg пересчитан', [
            'product_id' => $product->id,
            'price_per_kg' => $pricePerKg,
            'city_prices_updated' => $cityPricesCount,
        ]);
    }

    /**
     * Получить вес продукта в килограммах (конвертирует граммы в кг если нужно)
     */
    protected function getWeightInKilos(Product $product): float
    {
        $weightType = $product->getRawOriginal('weight_type');
        $weightType = is_string($weightType) ? trim($weightType) : $weightType;

        if ($weightType === WeightTypeEnum::Gram->value) {
            // Если вес в граммах, переводим в килограммы
            return (float) $product->weight / 1000;
        }
        
        // Если вес уже в килограммах, возвращаем как есть
        return (float) $product->weight;
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        try {
            $this->sitemapService->updateItem('product', $product, false);
        } catch (\Throwable $e) {
            Log::warning('Sitemap: ошибка при обновлении карты сайта после удаления продукта', [
                'product_id' => $product->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

