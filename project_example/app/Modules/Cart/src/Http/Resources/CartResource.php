<?php

namespace App\Modules\Cart\src\Http\Resources;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Services\CartFormattedItemsService;
use App\Modules\Cart\src\Services\SecondItemDiscountService;
use Bonus\Services\BonusService;
use Cart\Models\UserCart;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Product\Models\Product;
use Setting\Models\Setting;

class CartResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = UserCart::class;
    public function toArray($request)
    {
        $secondItemDiscountService = app(SecondItemDiscountService::class);
        $secondItemDiscount = $secondItemDiscountService->calculate($this->resource);

        $itemDiscounts = data_get($secondItemDiscount, 'item_discounts', []);
        $categoryDiscounts = data_get($secondItemDiscount, 'categories', []);
        $totalSecondItemDiscount = (float) data_get($secondItemDiscount, 'total_discount', 0.0);

        // Устанавливаем скидку на второй товар для всех товаров, которые участвуют в акции
        foreach ($this->items as $cartItem) {
            $item = $cartItem->item;
            if ($item instanceof Product && !$cartItem->is_gift) {
                $itemDiscountEntry = $itemDiscounts[$cartItem->id] ?? null;
                if ($itemDiscountEntry && isset($itemDiscountEntry['discount_per_unit']) && isset($itemDiscountEntry['base_price'])) {
                    // base_price - это цена товара после обычных скидок (getCartPrice())
                    // discount_per_unit - это сумма скидки на единицу товара
                    // Устанавливаем sale_price и sale_percent для скидки на второй товар
                    $item->second_item_sale_price_override = max(0, round($itemDiscountEntry['base_price'] - $itemDiscountEntry['discount_per_unit'], 2));
                    $item->second_item_sale_percent_override = $this->getSecondItemDiscountPercent($item);
                } else {
                    // Сбрасываем скидку на второй товар, если товар больше не участвует в акции
                    $item->second_item_sale_price_override = null;
                    $item->second_item_sale_percent_override = null;
                }
            }
        }

        // Скидка на второй товар теперь уже учтена в getCartPrice() через second_item_sale_price
        // Поэтому просто суммируем цены товаров
        $finalCartPrice = round(
            $this->items->sum(function ($cartItem) {
                $item = $cartItem->item;
                if ($cartItem->is_gift) {
                    return 0;
                }
                if (!$item || !($item instanceof CartItemPriceable)) {
                    return 0;
                }
                $cityQuantity = data_get($item, 'city_quantity', 0);
                if ($cityQuantity <= 0) {
                    return 0;
                }
                return $item->getCartPrice() * $cartItem->quantity;
            }),
            2
        );

        $items = $this->items->map(function ($cartItem) use ($itemDiscounts) {
                $item = $cartItem->item;

                if (!$item) {
                    return [];
                }

                $isCombo = $cartItem->item_type->value === CartItemTypeEnum::Combo->value;
                $isGift = (bool) $cartItem->is_gift;
                $isFirstOrderGift = false;

                $canDelete = !$isGift;
                if ($isGift && $item instanceof Product) {
                    // Проверяем, является ли это подарком за первый заказ
                    $isFirstOrderGift = $item->is_first_order_gift ?? false;
                    // Если это НЕ подарок за первый заказ, значит это подарок при покупке товара - можно удалять
                    $canDelete = !$isFirstOrderGift;
                }

                // second_item_sale_price уже установлен выше для всех товаров
                $resource = $isCombo ? new ComboCartResource($item) : new ProductCartResource($item);

                $giftProductIds = [];
                if (!$isCombo && $item instanceof Product) {
                    $item->loadMissing([
                        'giftProducts' => fn ($query) => $query->where('is_active', true),
                    ]);
                    $giftProductIds = $item->giftProducts?->pluck('id')->all() ?? [];
                }

                $cityQuantity = data_get($item, 'city_quantity');
                $isAvailable = $cityQuantity !== null ? $cityQuantity > 0 : true;

                // Получаем цену без скидки на второй товар (но с учетом обычных скидок)
                // Это нужно для расчета secondItemDiscountAmount в OrderService
                // Временно отключаем second_item_sale_price_override чтобы получить цену до скидки на второй товар
                $priceBeforeSecondDiscount = 0;
                if ($item instanceof CartItemPriceable) {
                    if ($item instanceof Product) {
                        $tempSecondItemSalePrice = $item->second_item_sale_price_override ?? null;
                        $item->second_item_sale_price_override = null;
                        $priceBeforeSecondDiscount = $item->sale_price ?? $item->total_price;
                        $item->second_item_sale_price_override = $tempSecondItemSalePrice;
                    } else {
                        $priceBeforeSecondDiscount = $item->getCartPrice();
                    }
                }
                
                // Теперь getCartPrice() учитывает second_item_sale_price если он установлен
                $price = ($item instanceof CartItemPriceable) ? $item->getCartPrice() : 0;
                $priceWithoutSale = $item->total_price ?? 0;

                $totalPriceWithoutSale = $isGift ? 0 : ($isAvailable ? round($priceWithoutSale * $cartItem->quantity, 2) : 0);
                $totalPrice = $isGift ? 0 : ($isAvailable ? round($price * $cartItem->quantity, 2) : 0);
                $totalPriceBeforeDiscount = $isGift ? 0 : ($isAvailable ? round($priceBeforeSecondDiscount * $cartItem->quantity, 2) : 0);
                
                // getSalePercent() теперь учитывает скидку на второй товар через second_item_sale_percent_override
                $salePercent = $isGift ? 0 : ($isAvailable && method_exists($item, 'getSalePercent') ? $item->getSalePercent() : 0);

                $itemDiscountEntry = $itemDiscounts[$cartItem->id] ?? null;
                $itemDiscountAmount = $itemDiscountEntry
                    ? round((float) data_get($itemDiscountEntry, 'discount', 0.0), 2)
                    : null;

                // Процент скидки на второй товар (для бейджа)
                $secondItemDiscountPercent = null;
                if ($item instanceof Product && $item->second_item_sale_percent_override !== null) {
                    $secondItemDiscountPercent = $item->second_item_sale_percent_override;
                }

                // Участвует в промоакции (promotion_product / promotion_combo) — промокод на такие товары не распространяется
                $isPromotional = !$isGift && method_exists($item, 'promotion') && $item->promotion()->exists();

                return [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'is_available' => $isAvailable,
                    'city_quantity' => $cityQuantity,
                    'total_price_without_sale' => $totalPriceWithoutSale,
                    'total_price' => $totalPrice,
                    'total_price_before_discount' => $totalPriceBeforeDiscount, // Цена ДО скидки на второй товар, но С учетом обычных скидок
                    'sale_percent' => $salePercent, // Теперь включает скидку на второй товар
                    'is_combo' => $isCombo,
                    'item' => $resource,
                    'item_type' => $cartItem->item_type,
                    'is_gift' => $isGift,
                    'is_first_order_gift' => $isFirstOrderGift,
                    'can_delete' => $canDelete,
                    'gift_product_ids' => $giftProductIds,
                    'second_item_discount_amount' => $itemDiscountAmount,
                    'second_item_discount_percent' => $secondItemDiscountPercent,
                    'is_promotional' => $isPromotional,
                ];
            })->filter()->values(); // <-- filter(), чтобы убрать null

        $bonusSpendLimit = 0;
        if ($finalCartPrice > 0 && auth()->check()) {
            $bonusService = app(BonusService::class);
            $availableBonus = $bonusService->getAvailableBalance(auth()->user());

            $maxBonusPercent = round($finalCartPrice * 0.25, 2);
            $bonusSpendLimit = max(0, min($finalCartPrice, $maxBonusPercent));
            $bonusSpendLimit = min($bonusSpendLimit, $availableBonus);
        }

        return [
            'items' => $items,
            'formatedItems' => CartFormattedItemsService::format($items instanceof Collection ? $items : collect($items)),

            'cartQuantity' => $this->items->sum(function ($cartItem) {
                $item = $cartItem->item;
                if (!$item) return 0;
                $cityQuantity = data_get($item, 'city_quantity');
                return ($cityQuantity !== null && $cityQuantity > 0) ? $cartItem->quantity : 0;
            }),

            'cartPrice' => $finalCartPrice,

            'cartWeight' => round($this->items->sum(function ($cartItem) {
                $item = $cartItem->item;
                if (!$item || (!$cartItem->is_gift && data_get($item, 'city_quantity', 0) <= 0)) {
                    return 0;
                }
                $weight = $item->weight ?? 0;
                return $weight * $cartItem->quantity;
            }), 2),
            'second_item_discount_total' => round($totalSecondItemDiscount, 2),
            'second_item_discount_categories' => array_values(array_map(fn ($category) => [
                'id' => data_get($category, 'id'),
                'name' => data_get($category, 'name'),
                'discount' => round((float) data_get($category, 'discount', 0.0), 2),
            ], $categoryDiscounts)),
            'bonus_spent_limit' => (int) floor($bonusSpendLimit),

        ];
    }

    protected static ?int $secondItemPercentCache = null;

    protected function hasSecondItemDiscountCategory($item): bool
    {
        if (!$item instanceof Product) {
            return false;
        }

        $item->loadMissing('categories');

        return (bool) $item->categories
            ->first(fn ($category) => $category->has_second_item_discount ?? false);
    }


    protected function getSecondItemDiscountPercent($item): ?int
    {
        if (!$this->hasSecondItemDiscountCategory($item)) {
            return null;
        }

        if (is_null(self::$secondItemPercentCache)) {
            $value = Setting::where('key', 'second_item_discount_percent')->value('value');
            self::$secondItemPercentCache = is_null($value) ? 10 : (int) $value;
        }

        return self::$secondItemPercentCache;
    }
}
