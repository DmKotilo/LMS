<?php

namespace App\Modules\Order\src\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Order\Models\OrderItem;
use Product\Models\Product;
use Setting\Models\Setting;

class OrderItemResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = OrderItem::class;

    public function toArray(Request $request): array
    {
        $priceWithoutSale = ($this->unit_sale_percent != 0 || $this->unit_sale_percent != null) 
            ? $this->price_for_unit_without_sale * $this->quantity 
            : null;
        
        $priceForUnitWithoutSale = ($this->unit_sale_percent != 0 || $this->unit_sale_percent != null) 
            ? $this->price_for_unit_without_sale 
            : null;

        $isCombo = $this->item_type->value === \Combo\Models\Combo::class;

        return [
            'id'  => $this->id,
            'type'  => class_basename($this->item_type->value),
            'price' => $this->price, //ЦЕНА ЗА ВСЁ КЛИЧЕСТВО С УЧЕТОМ СКИДКИ ЕСЛИ ОНА ЕСТЬ
            'price_without_sale' => $priceWithoutSale, //ЦЕНА ЗА ВСЁ КЛИЧЕСТВО БЕЗ СКИДКИ
            'price_for_unit' => $this->price_for_unit, //ЦЕНА ЗА ЕДИНИЦУ ТОВАРА СО СКИДКУ
            'price_for_unit_without_sale' => $priceForUnitWithoutSale, //ЦЕНА ЗА ЕДИНИЦУ ТОВАРА БЕЗ СКИДКИ
            'unit_sale_percent' => (int) $this->unit_sale_percent,
            // Алиасы для совместимости с корзиной
            'total_price' => $this->price, // Цена за всё количество со скидкой
            'total_price_without_sale' => $priceWithoutSale, // Цена за всё количество без скидки
            'sale_percent' => (int) $this->unit_sale_percent, // Процент скидки
            'is_combo' => $isCombo, // Для совместимости с корзиной
            'quantity' => $this->quantity,
            'weight' => $this->weight,
            'weight_for_unit' => $this->weight_for_unit,
            'weight_type' => $this->weight_type,
            'is_gift' => (bool) $this->is_gift,
            'can_delete' => $this->getCanDelete(),
            'second_item_discount_percent' => $this->getSecondItemDiscountPercent(),
            'second_item_discount_amount' => $this->getSecondItemDiscountAmount(),
            'item'  => $this->whenLoaded('item', function () use ($priceForUnitWithoutSale) {
                return match ($this->item_type->value) {
                    \Product\Models\Product::class => new ProductOrderResource($this->item, [
                        'order_item_price' => $this->price_for_unit, // Цена за единицу со скидкой
                        'order_item_price_without_sale' => $priceForUnitWithoutSale, // Цена за единицу без скидки
                        'order_item_sale_percent' => (int) $this->unit_sale_percent, // Процент скидки
                    ]),
                    \Combo\Models\Combo::class => new ComboOrderResource($this->item),
                    default => null,
                };
            }),
        ];
    }

    /**
     * Определяет, можно ли удалять подарок
     * Подарок за первый заказ нельзя удалять (can_delete = false)
     * Подарок при покупке товара можно удалять (can_delete = true)
     */
    protected function getCanDelete(): bool
    {
        if (!$this->is_gift) {
            return true; 
        }

        $item = $this->item;
        if ($item instanceof Product) {
            // Если это подарок за первый заказ, нельзя удалять
            $isFirstOrderGift = $item->is_first_order_gift ?? false;
            // Если это НЕ подарок за первый заказ, значит это подарок при покупке товара - можно удалять
            return !$isFirstOrderGift;
        }

        return false;
    }

    protected static ?int $secondItemPercentCache = null;

    protected function getSecondItemDiscountPercent(): ?int
    {
        $item = $this->item;

        if (!$item instanceof Product) {
            return null;
        }

        $item->loadMissing('categories');

        $hasDiscountCategory = (bool) $item->categories
            ?->first(fn ($category) => $category->has_second_item_discount ?? false);

        if (!$hasDiscountCategory) {
            return null;
        }

        if (is_null(self::$secondItemPercentCache)) {
            $value = Setting::where('key', 'second_item_discount_percent')->value('value');
            self::$secondItemPercentCache = is_null($value) ? 10 : (int) $value;
        }

        return self::$secondItemPercentCache;
    }

    protected function getSecondItemDiscountAmount(): ?float
    {
        $percent = $this->getSecondItemDiscountPercent();

        if (is_null($percent)) {
            return null;
        }

        // Если есть скидка на второй товар, то unit_sale_percent содержит её процент
        // Нужно рассчитать цену до применения скидки на второй товар
        // Это цена после обычной скидки (если была), но до скидки на второй товар
        
        // Если unit_sale_percent равен проценту скидки на второй товар, значит обычной скидки не было
        // В этом случае цена до скидки на второй товар = price_for_unit_without_sale
        // Иначе нужно рассчитать цену с учетом обычной скидки
        
        $salePricePerUnit = $this->getSalePriceBeforeSecondDiscount();
        $quantity = max(1, (int) $this->quantity);

        if (is_null($salePricePerUnit) || $quantity <= 0) {
            return null;
        }

        $priceBeforeDiscount = $salePricePerUnit * $quantity;
        $discountAmount = round($priceBeforeDiscount - (float) $this->price, 2);

        return $discountAmount > 0 ? $discountAmount : null;
    }

    protected function getSalePriceBeforeSecondDiscount(): ?float
    {
        $unitBasePrice = $this->price_for_unit_without_sale;

        if (is_null($unitBasePrice)) {
            return null;
        }

        // Если unit_sale_percent равен проценту скидки на второй товар, значит обычной скидки не было
        // В этом случае цена до скидки на второй товар = базовая цена
        $secondItemPercent = $this->getSecondItemDiscountPercent();
        $unitSalePercent = (float) ($this->unit_sale_percent ?? 0);
        
        // Если процент скидки равен проценту скидки на второй товар, значит это только скидка на второй товар
        // В этом случае цена до скидки на второй товар = базовая цена
        if ($secondItemPercent !== null && abs($unitSalePercent - $secondItemPercent) < 0.01) {
            return round($unitBasePrice, 2);
        }
        
        // Иначе есть обычная скидка, применяем её
        $multiplier = max(0, (100 - $unitSalePercent) / 100);
        return round($unitBasePrice * $multiplier, 2);
    }
}
