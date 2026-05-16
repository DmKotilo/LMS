<?php

declare(strict_types=1);

namespace App\Modules\Cart\src\Services;

use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use Cart\Models\UserCart;
use Category\Models\Category;
use Product\Models\Product;
use Setting\Models\Setting;

class SecondItemDiscountService
{
    private const DEFAULT_PERCENT = 10;
    protected ?float $percentCache = null;

    public function calculate(UserCart $cart): array
    {
        $cart->loadMissing(['items.item']);

        $eligibleUnits = [];
        $eligibleUnitsWithoutSale = []; // Товары без обычной скидки (для применения скидки)

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->item;

            if (!$item instanceof Product) {
                continue;
            }

            $item->loadMissing('categories');

            if ($cartItem->is_gift) {
                continue;
            }

            if ($cartItem->item_type->value !== CartItemTypeEnum::Product->value) {
                continue;
            }

            // Проверяем, есть ли обычная скидка (акция)
            // Временно отключаем second_item_sale_price_override чтобы проверить обычную скидку
            $tempSecondItemSalePrice = $item->second_item_sale_price_override ?? null;
            $item->second_item_sale_price_override = null;
            $hasRegularSale = $item->sale_price !== null;

            $price = (float) $item->total_price;

            // Восстанавливаем second_item_sale_price_override после получения цены
            $item->second_item_sale_price_override = $tempSecondItemSalePrice;

            if ($price <= 0) {
                continue;
            }

            $cityQuantity = (int) data_get($item, 'city_quantity', 0);
            if ($cityQuantity <= 0) {
                continue;
            }

            $eligibleCategories = $item->categories
                ->filter(fn (Category $category) => (bool) $category->has_second_item_discount)
                ->sortBy('id');

            if ($eligibleCategories->isEmpty()) {
                continue;
            }

            $category = $eligibleCategories->first();
            $quantity = max(0, (int) $cartItem->quantity);

            for ($i = 0; $i < $quantity; $i++) {
                $unit = [
                    'cart_item_id' => $cartItem->id,
                    'price' => $price,
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'has_regular_sale' => $hasRegularSale,
                ];

                $eligibleUnits[] = $unit;

                if (!$hasRegularSale) {
                    $eligibleUnitsWithoutSale[] = $unit;
                }
            }
        }

        if (count($eligibleUnits) < 2) {
            return $this->emptyResult();
        }

        if (count($eligibleUnitsWithoutSale) === 0) {
            return $this->emptyResult();
        }

        $categoryDiscounts = [];
        $itemDiscounts = [];
        $totalDiscount = 0.0;

        foreach ($this->buildCategoryPairs($eligibleUnits, $eligibleUnitsWithoutSale) as $pair) {
            $discount = round($pair['discount_amount'], 2);

            if ($discount <= 0) {
                continue;
            }

            $totalDiscount += $discount;
            $categoryDiscounts[] = [
                'id' => $pair['category_id'],
                'name' => $pair['category_name'],
                'discount' => $discount,
            ];

            $cartItemId = $pair['discount_unit']['cart_item_id'];
            if (!isset($itemDiscounts[$cartItemId])) {
                $itemDiscounts[$cartItemId] = [
                    'discount' => 0.0,
                    'discount_per_unit' => 0.0,
                    'units' => 0,
                    'base_price' => $pair['discount_unit']['price'],
                ];
            }

            $itemDiscounts[$cartItemId]['discount'] += $discount;
            $itemDiscounts[$cartItemId]['units'] += 1;
            // Скидка на единицу товара (цена самого дешевого товара * процент)
            // Это цена за единицу товара со скидкой на второй товар
            $itemDiscounts[$cartItemId]['discount_per_unit'] = $pair['discount_unit']['price'] * $this->getDiscountPercent();
            // Базовая цена для расчета (цена до скидки на второй товар, но с учетом обычных скидок)
            $itemDiscounts[$cartItemId]['base_price'] = $pair['discount_unit']['price'];
        }

        if ($totalDiscount <= 0) {
            return $this->emptyResult();
        }

        return [
            'total_discount' => round($totalDiscount, 2),
            'item_discounts' => $itemDiscounts,
            'categories' => $categoryDiscounts,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'total_discount' => 0.0,
            'item_discounts' => [],
            'categories' => [],
        ];
    }

    private function buildCategoryPairs(array $eligibleUnits, array $eligibleUnitsWithoutSale): array
    {
        $grouped = [];
        $groupedWithoutSale = [];

        // Группируем все товары по категориям
        foreach ($eligibleUnits as $unit) {
            $grouped[$unit['category_id']][] = $unit;
        }

        // Группируем товары без обычной скидки по категориям
        foreach ($eligibleUnitsWithoutSale as $unit) {
            $groupedWithoutSale[$unit['category_id']][] = $unit;
        }

        $pairs = [];

        foreach ($grouped as $categoryId => $units) {
            // Нужно минимум 2 единицы товаров в категории
            if (count($units) < 2) {
                continue;
            }

            // Нужен хотя бы один товар без обычной скидки в этой категории
            $unitsWithoutSale = $groupedWithoutSale[$categoryId] ?? [];
            if (count($unitsWithoutSale) === 0) {
                continue;
            }

            // Сортируем все товары по цене
            usort($units, static fn (array $a, array $b) => $a['price'] <=> $b['price']);

            // Сортируем товары без обычной скидки по цене
            usort($unitsWithoutSale, static fn (array $a, array $b) => $a['price'] <=> $b['price']);

            // Берем самый дешевый товар БЕЗ обычной скидки для применения скидки
            $cheapestUnitWithoutSale = $unitsWithoutSale[0];

            // Берем самый дорогой товар из всех (может быть с обычной скидкой) для пары
            $pairedUnit = $units[count($units) - 1];

            if (!$cheapestUnitWithoutSale || !$pairedUnit) {
                continue;
            }

            // Скидка применяется к самому дешевому товару БЕЗ обычной скидки
            $discountAmount = $cheapestUnitWithoutSale['price'] * $this->getDiscountPercent();

            if ($discountAmount <= 0) {
                continue;
            }

            $pairs[] = [
                'category_id' => $categoryId,
                'category_name' => $cheapestUnitWithoutSale['category_name'],
                'discount_amount' => $discountAmount,
                'discount_unit' => $cheapestUnitWithoutSale,
                'paired_with' => $pairedUnit,
            ];
        }

        return $pairs;
    }

    protected function getDiscountPercent(): float
    {
        if ($this->percentCache !== null) {
            return $this->percentCache;
        }

        $value = Setting::where('key', 'second_item_discount_percent')->value('value');
        $percent = is_null($value) ? self::DEFAULT_PERCENT : (float) $value;

        return $this->percentCache = max(0, $percent) / 100;
    }
}
