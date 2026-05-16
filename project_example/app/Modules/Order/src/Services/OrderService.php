<?php

namespace Order\Services;

use Address\Model\Address;
use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Http\Resources\CartResource;
use App\Modules\Cart\src\Http\Resources\ComboCartResource;
use App\Modules\Cart\src\Http\Resources\ProductCartResource;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Combo\Models\Combo;
use Delivery\Models\DeliveryZone;
use Delivery\Models\PickupLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Order\Models\OrderPromocode;
use Product\Models\Product;

class OrderService {
    static public function getOrderCollect($cart, $product_id = null, $combo_id = null): Collection | \Illuminate\Support\Collection
    {
        if ($product_id || $combo_id) { // товар для 1 клика
            if ($product_id) {
                $itemType = CartItemTypeEnum::Product;
                $item = Product::find($product_id);
            } else if ($combo_id) {
                $itemType = CartItemTypeEnum::Combo;
                $item = Combo::with('products')->find($combo_id);
            }

            if (!$item) {
                return \Illuminate\Validation\ValidationException::withMessages([
                    'error' => 'Товар не найден'
                ]);
            }

            $price = ($item instanceof CartItemPriceable) ? $item->getCartPrice() : 0;
            $isCombo = $itemType->value === CartItemTypeEnum::Combo->value;
            $resource = $isCombo ? new ComboCartResource($item) : new ProductCartResource($item);

            $isAvailable = $item->city_quantity > 0;

            $totalPrice = ($item instanceof CartItemPriceable) ? $item->total_price : ($item->price ?? 0);
            $isPromotional = method_exists($item, 'promotion') && $item->promotion()->exists();

            $cartItems = collect([(object) [
                'id' => $item->id,
                'quantity' => 1,
                'is_available' => $isAvailable,
                'total_price_without_sale' => $isAvailable ? ($price * 1) : 0,
                'total_price' => $isAvailable ? ($item->sale_price ?? $totalPrice) : 0,
                'total_price_before_discount' => $isAvailable ? ($item->sale_price ?? $totalPrice) : 0,
                'sale_percent' => $item->getSalePercent(),
                'is_combo' => $isCombo,
                'item' => $resource,
                'item_type' => $itemType->value,
                'is_gift' => false,
                'second_item_discount_amount' => 0,
                'is_promotional' => $isPromotional,
            ]]);

            // Добавляем подарки из giftProducts для быстрой покупки
            if ($item instanceof Product) {
                $item->load('giftProducts');
                $giftProducts = $item->giftProducts()->where('is_active', true)->get();

                foreach ($giftProducts as $giftProduct) {
                    $cityQuantity = $giftProduct->city_quantity ?? 0;
                    if ($cityQuantity <= 0) {
                        continue;
                    }

                    $giftPrice = ($giftProduct instanceof CartItemPriceable) ? $giftProduct->getCartPrice() : 0;
                    $giftResource = new ProductCartResource($giftProduct);
                    $giftIsAvailable = $giftProduct->city_quantity > 0;

                    $cartItems->push((object) [
                        'id' => $giftProduct->id,
                        'quantity' => 1,
                        'is_available' => $giftIsAvailable,
                        'total_price_without_sale' => 0,
                        'total_price' => 0,
                        'total_price_before_discount' => 0,
                        'sale_percent' => 0,
                        'is_combo' => false,
                        'item' => $giftResource,
                        'item_type' => CartItemTypeEnum::Product->value,
                        'is_gift' => true,
                        'second_item_discount_amount' => 0,
                        'is_promotional' => false,
                    ]);
                }
            }
        } else {
            // Загружаем корзину с полиморфными items
            $cart->load('items.item');

            // ❌ УДАЛЕНО: Явная загрузка products не нужна
            // Теперь getTotalPriceAttribute() сам загружает products при необходимости

            $cartResource = CartResource::make($cart);
            $cartObj = (object) $cartResource->resolve();

            if (empty($cartObj->items)) {
                return \Illuminate\Validation\ValidationException::withMessages([
                    'error' => 'В корзине нет товаров для заказа'
                ]);
            }

            $cartItems = collect($cartObj->items)->map(function($ci) {
                $ci = (object) $ci;
                $ci->is_available = $ci->item->city_quantity > 0;

                // если товар недоступен, обнуляем цену
                if (!$ci->is_available) {
                    $ci->total_price = 0;
                    $ci->total_price_without_sale = 0;
                }

                return $ci;
            });
        }

        return $cartItems;
    }

    static public function calculateOrder(
        $collectCartItems,
        ?OrderPromocode $promocode = null,
        ?DeliveryZone $deliveryZone = null,
        ?Address $address = null,
        ?PickupLocation $pickupLocation = null,
        $delivery_time_from = null,
        $delivery_time_to = null,
        $delivery_date = null,
        ?string $deliveryType = null,


        ?int $cityIdFromRequest = null,
        bool $useExpiringBonuses = false,
        ?float $expiringBonusAmount = null,
        ?float $availableBonus = null
    ) {
        $expiringBonusAmount = $expiringBonusAmount !== null ? max(0, round($expiringBonusAmount, 2)) : null;
        $availableBonus = $availableBonus !== null ? max(0, round($availableBonus, 2)) : null;
        // Отбираем только доступные товары
        $availableItems = $collectCartItems->filter(fn($ci) => $ci->is_available);

        $secondItemDiscountAmount = round(
            $collectCartItems
                ->filter(fn($ci) => empty($ci->is_gift)) // Исключаем подарки
                ->sum(function ($ci) {
                    $before = (float) ($ci->total_price_before_discount ?? $ci->total_price ?? 0);
                    $after = (float) ($ci->total_price ?? 0);

                    return max(0, $before - $after);
                }),
            2
        );

        // Если не передали $deliveryZone, пробуем взять из адреса
        if ($deliveryZone == null && $address?->deliveryZone) {
            $deliveryZone = $address->deliveryZone;
//            dd($deliveryZone);
        }

        $order_detail = $delivery_detail = [];

        // Сумма товаров (исключаем подарки по флагу is_gift и по цене = 0)
        $order_detail['cart_sum'] = round($availableItems
            ->filter(fn($ci) => empty($ci->is_gift)) // Явно исключаем подарки
            ->sum(fn($ci) => $ci->total_price), 2);

        // Сумма только товаров без промоакции — промокод применяется только к ним
        $cart_sum_for_promocode = round($availableItems
            ->filter(fn($ci) => empty($ci->is_gift) && empty($ci->is_promotional ?? false))
            ->sum(fn($ci) => $ci->total_price), 2);

        $order_detail['second_item_discount'] = $secondItemDiscountAmount;

        $delivery_price = 0;
        if ($deliveryZone && $order_detail['cart_sum'] < $deliveryZone->min_order_sum) {
            $delivery_price = $deliveryZone->delivery_price;
        }
        $order_detail['delivery_price'] = $delivery_price;

        $detailCityId = null;
        $detailCityName = null;

        if ($address) {
            $detailCityId = $address->city_id;
            $detailCityName = $address->city->name ?? null;
        } elseif ($pickupLocation && $cityIdFromRequest) {
            $detailCityId = $cityIdFromRequest;
            $detailCityName = $pickupLocation->city_relation->name ?? null;
        } elseif ($pickupLocation && !$address) {
            $detailCityId = $pickupLocation->city_id;
            $detailCityName = $pickupLocation->city_relation->name ?? null;
        }

        $delivery_detail = [
            'delivery_date' => $delivery_date ?? null, //выбранная пользователем
            'delivery_time_from' => $delivery_time_from ?? null,
            'delivery_time_to' => $delivery_time_to ?? null,
//            'city' => $address->city->name ?? null,
            'city' => $detailCityName,
            'city_id' => $detailCityId,

            'address_full' => $address->final_address ?? null,
            'delivery_zone_name' => $deliveryZone->name ?? null,
            'delivery_zone_description' => $deliveryZone->description ?? null,
            'delivery_zone_min_order_sum' => $deliveryZone->min_order_sum ?? null,
            'delivery_zone_time_min' => self::getDeliveryZoneMinDaysForNow($deliveryZone?->delivery_time, $deliveryType),//минимально установлення дата для зоны доставки
            'pickup_location_name' => $pickupLocation->title ?? null,
            'pickup_location_address' => $pickupLocation->fullAddress ?? null,
            'pickup_location_time_min' => $pickupLocation->delivery_time ?? null,
            'delivery_price' => $delivery_price,
        ];

        // Вес заказа (суммируем вес всех товаров с учетом единиц измерения, приводим к килограммам)
        $order_detail['weight'] = round($availableItems->sum(function($ci) {
            $item = $ci->item;
            if (!$item) {
                return 0;
            }
            $weight = $item->weight ?? 0;
            if ($weight <= 0) {
                return 0;
            }
            // Конвертируем вес в килограммы
            $weightInKg = self::convertWeightToKilograms($weight, $item->weight_type ?? null);
            return $weightInKg * $ci->quantity;
        }), 2);

 
        $order_detail['quantity'] = $collectCartItems->sum(function ($ci) {
            $item = $ci->item;
            if (!$item) {
                return 0;
            }
            $cityQuantity = data_get($item, 'city_quantity');
            return ($cityQuantity !== null && $cityQuantity > 0) ? $ci->quantity : 0;
        });

        // Промокод: только к товарам без промоакции; если все товары со скидкой — ошибка
        if ($promocode) {
            if ($cart_sum_for_promocode <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'error' => ['Промокод нельзя применить, так как все товары в заказе уже имеют скидку.'],
                ]);
            }
            $order_detail['promocode_discount'] = round($cart_sum_for_promocode / 100 * $promocode->percent, 2);
        } else {
            $order_detail['promocode_discount'] = null;
        }

        // Сумма товаров после всех скидок и промокода (БЕЗ доставки)
        $cartSumAfterDiscounts = round(
            $order_detail['cart_sum'] - ($order_detail['promocode_discount'] ?? 0),
            2
        );

        // Итоговая сумма заказа (с доставкой, но без бонусов)
        $baseFinal = round(
            $cartSumAfterDiscounts + $order_detail['delivery_price'],
            2
        );

        // bonus_spent_limit рассчитывается от суммы товаров ПОСЛЕ скидок и промокода, но БЕЗ доставки
        // Если пользователь не авторизован ($availableBonus === null), то bonus_spent_limit = 0
        $bonusSpendLimit = 0;
        if ($availableBonus !== null && $cartSumAfterDiscounts > 0) {
            $maxBonusPercent = round($cartSumAfterDiscounts * 0.25, 2);
            $bonusSpendLimit = max(0, min($cartSumAfterDiscounts, $maxBonusPercent));
            $bonusSpendLimit = min($bonusSpendLimit, $availableBonus);
        }

        // Округляем в меньшую сторону до целого числа без дробной части
        $order_detail['bonus_spent_limit'] = (int) floor($bonusSpendLimit);

        // Списание бонусов происходит только если use_expiring_bonuses = true
        // Списываем все доступные бонусы до лимита (не только истекающие)
        $appliedBonus = 0;
        if ($useExpiringBonuses && $availableBonus !== null && $availableBonus > 0) {
            // Списываем все доступные бонусы, но не больше лимита
            $appliedBonus = min($availableBonus, $order_detail['bonus_spent_limit']);
        }

        // Округляем в меньшую сторону до целого числа без дробной части
        $appliedBonus = (int) floor($appliedBonus);

        // Моя скидка = сумма списанных бонусов (явно обнуляется если use_expiring_bonuses = false)
        $order_detail['personal_discount'] = $appliedBonus;
        $order_detail['bonus_spent'] = $appliedBonus;
        $order_detail['price_final'] = round($baseFinal - $appliedBonus, 2);

        if ($order_detail['price_final'] < 0) {
            $order_detail['price_final'] = 0;
        }

        return [
            'delivery_detail' => $delivery_detail,
            'order_detail' => $order_detail
        ];
    }

    /**
     * Минимальное кол-во дней для курьерской доставки с учётом времени сервера.
     * Если курьер и текущее серверное время строго больше 19:00 — добавляем +1 день.
     */
    protected static function getDeliveryZoneMinDaysForNow(mixed $baseDays, ?string $deliveryType): ?int
    {
        if ($baseDays === null) {
            return null;
        }

        $days = (int) $baseDays;
        if ($days < 0) {
            $days = 0;
        }

        if ($deliveryType !== 'courier') {
            return $days;
        }

        $now = Carbon::now();
        $cutoff = $now->copy()->setTime(19, 0, 0);

        if ($now->greaterThan($cutoff)) {
            $days += 1;
            Log::channel('single')->info('Delivery cutoff applied (+1 day to courier min delivery days)', [
                'now' => $now->toDateTimeString(),
                'app_timezone' => config('app.timezone'),
                'php_timezone' => date_default_timezone_get(),
                'cutoff' => $cutoff->toDateTimeString(),
                'base_days' => (int) $baseDays,
                'final_days' => $days,
            ]);
        }

        return $days;
    }

    /**
     * Конвертирует вес в килограммы с учетом единицы измерения
     *
     * @param float|null $weight Вес товара
     * @param WeightTypeEnum|string|null $weightType Тип веса ('кг' или 'г')
     * @return float Вес в килограммах
     */
    protected static function convertWeightToKilograms(?float $weight, WeightTypeEnum|string|null $weightType): float
    {
        if (!$weight || $weight <= 0) {
            return 0;
        }

        // Если передан enum, получаем его значение
        if ($weightType instanceof WeightTypeEnum) {
            $weightType = $weightType->value;
        }

        // Если тип веса в граммах, конвертируем в килограммы
        if ($weightType === 'г' || $weightType === WeightTypeEnum::Gram->value) {
            return $weight / 1000;
        }

        // Если тип веса в килограммах или не указан, возвращаем как есть
        return $weight;
    }

}


