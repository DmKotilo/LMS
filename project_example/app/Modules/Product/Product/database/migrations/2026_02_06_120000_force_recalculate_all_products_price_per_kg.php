<?php

use Illuminate\Database\Migrations\Migration;
use Product\Models\Product;

return new class extends Migration {
    /**
     * Принудительно пересчитать price_per_kg у всех товаров с типом цены «руб/кг» или «руб/порция»
     * и у связанных записей city_product_prices.
     */
    public function up(): void
    {
        Product::query()
            ->select(['id', 'price', 'weight', 'weight_type', 'price_type'])
            ->whereIn('price_type', ['руб/кг', 'руб/порция'])
            ->chunkById(200, function ($products): void {
                foreach ($products as $product) {
                    $product->recalculatePricePerKg();
                }
            });
    }

    public function down(): void
    {
        // Откат не меняет данные — пересчёт идемпотентный
    }
};
