<?php

namespace App\Observers;

use App\Modules\City\src\Models\CityProductPrice;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Illuminate\Support\Facades\DB;

class CityProductPriceObserver
{
    /**
     * Handle the CityProductPrice "saved" event.
     * price_per_kg пересчитывается при каждом сохранении для товаров с типом "руб/кг".
     */
    public function saved(CityProductPrice $cityProductPrice): void
    {
        $this->recalculatePricePerKg($cityProductPrice);
    }

    /**
     * Пересчитать price_per_kg для цены по городу
     */
    protected function recalculatePricePerKg(CityProductPrice $cityProductPrice): void
    {
        // Загружаем связанный продукт
        $product = $cityProductPrice->product;
        
        if (!$product) {
            return;
        }

        $pricePerKg = null;
        
        // Если тип цены продукта "руб/кг", рассчитываем цену
        if ($product->price_type === PriceTypeEnum::Per_Kilo && $cityProductPrice->price && $product->weight) {
            // Получаем вес в килограммах (конвертируем граммы в кг если нужно)
            $weightInKilos = $this->getWeightInKilos($product);
            $pricePerKg = round($cityProductPrice->price * $weightInKilos, 2);
        }

        // Обновляем price_per_kg без событий, чтобы избежать рекурсии
        DB::table('city_product_prices')
            ->where('id', $cityProductPrice->id)
            ->update(['price_per_kg' => $pricePerKg]);
    }

    /**
     * Получить вес продукта в килограммах (конвертирует граммы в кг если нужно)
     */
    protected function getWeightInKilos($product): float
    {
        if ($product->weight_type === WeightTypeEnum::Gram) {
            // Если вес в граммах, переводим в килограммы
            return (float) $product->weight / 1000;
        }
        
        // Если вес уже в килограммах, возвращаем как есть
        return (float) $product->weight;
    }

    /**
     * Handle the CityProductPrice "deleted" event.
     */
    public function deleted(CityProductPrice $cityProductPrice): void
    {
        //
    }
}
