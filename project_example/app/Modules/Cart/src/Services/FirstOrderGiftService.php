<?php

namespace App\Modules\Cart\src\Services;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Http\Resources\ProductCartResource;
use App\Modules\Cart\src\Models\CartItem;
use Cart\Models\UserCart;
use Illuminate\Support\Collection;
use Order\Models\Order;
use Product\Models\Product;
use Setting\Models\Setting;
use User\Models\User;

class FirstOrderGiftService
{
    public const GIFT_TYPE = 'gift';

    protected ?float $minOrderSumCache = null;
    protected ?Product $giftProductCache = null;

    public function syncGiftForCart(UserCart $cart, ?User $user = null, ?string $email = null, ?string $phone = null): void
    {
        $cart->loadMissing('items.item');
        // Для расчета суммы заказа учитываем только НЕ-подарки
        // Подарки из giftProducts и подарок за первый заказ не учитываются в сумму
        $giftItem = $cart->items->first(fn (CartItem $item) => $this->isFirstOrderGiftCartItem($item));
        $nonGiftItems = $cart->items
            ->filter(fn (CartItem $item) => !$item->is_gift && $item->item !== null);
        if ($nonGiftItems->isEmpty()) {
            $this->removeGift($giftItem);
            return;
        }

        $orderSum = $nonGiftItems->reduce(function (float $carry, CartItem $item) {
            $price = $this->resolveCartItemPrice($item);
            return $carry + ($price * $item->quantity);
        }, 0.0);

        if (!$this->canApplyGift($orderSum, $user, $email, $phone)) {
            $this->removeGift($giftItem);
            return;
        }

        $giftProduct = $this->getGiftProduct();
        if (!$giftProduct) {
            $this->removeGift($giftItem);
            return;
        }
        if ($giftItem) {
            $giftItem->update([
                'item_type' => CartItemTypeEnum::Product->value,
                'item_id' => $giftProduct->id,
                'quantity' => 1,
                'is_gift' => true,
            ]);

            return;
        }

        $cart->items()->create([
            'item_type' => CartItemTypeEnum::Product->value,
            'item_id' => $giftProduct->id,
            'quantity' => 1,
            'is_gift' => true,
        ]);
    }

    public function appendGiftToCollection(Collection $items, ?User $user = null, ?string $email = null, ?string $phone = null): Collection
    {
        $items = $items->values();
        $giftProduct = $this->getGiftProduct();
        $firstOrderGiftId = $giftProduct ? $giftProduct->id : null;

        // Для расчета суммы заказа учитываем только НЕ-подарки
        // Подарки из giftProducts и подарок за первый заказ не учитываются в сумму
        $nonGiftItems = $items->filter(function ($item) {
            return empty($item->is_gift) && !empty($item->is_available);
        });

        $orderSum = $nonGiftItems->reduce(function (float $carry, $item) {
            $price = (float) ($item->total_price_before_discount ?? $item->total_price ?? 0);

            return $carry + $price;
        }, 0.0);

        $canApply = $this->canApplyGift($orderSum, $user, $email, $phone);
        $giftProduct = $canApply ? $this->getGiftProduct() : null;
        $firstOrderGiftId = $giftProduct ? $giftProduct->id : null;

        if (!$canApply || !$giftProduct) {
            return $items->reject(fn ($item) => $this->isCollectionFirstOrderGiftItem($item))->values();
        }

        $hasGift = $items->contains(fn ($item) => $this->isCollectionFirstOrderGiftItem($item));
        if ($hasGift) {
            return $items->map(function ($item) use ($giftProduct) {
                if ($this->isCollectionFirstOrderGiftItem($item)) {
                    $item->item = new ProductCartResource($giftProduct);
                    $item->item_type = CartItemTypeEnum::Product;
                    $item->total_price = 0;
                    $item->total_price_without_sale = 0;
                    $item->total_price_before_discount = 0;
                    $item->sale_percent = 0;
                    $item->is_combo = false;
                    $item->is_available = true;
                    $item->city_quantity = $giftProduct->city_quantity;
                    $item->can_delete = false;
                    $item->second_item_discount_amount = 0;
                    $item->second_item_discount_percent = null;
                }
                return $item;
            })->values();
        }

        $items->push((object) [
            'id' => null,
            'quantity' => 1,
            'is_available' => true,
            'city_quantity' => $giftProduct->city_quantity,
            'total_price_without_sale' => 0,
            'total_price' => 0,
            'total_price_before_discount' => 0,
            'sale_percent' => 0,
            'is_combo' => false,
            'item' => new ProductCartResource($giftProduct),
            'item_type' => CartItemTypeEnum::Product,
            'item_id' => $giftProduct->id,
            'is_gift' => true,
            'can_delete' => false,
        ]);

        return $items;
    }

    protected function canApplyGift(float $orderSum, ?User $user = null, ?string $email = null, ?string $phone = null): bool
    {
        if ($orderSum < $this->getMinOrderSum()) {
            return false;
        }

        $userId = $user?->id;
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedPhone = $this->normalizePhone($phone);

        if ($this->isGiftBlockedForIdentity($userId, $normalizedEmail, $normalizedPhone)) {
            return false;
        }

        if ($this->hasExistingOrderForIdentity($userId, $normalizedEmail, $normalizedPhone, $user)) {
            $this->blockGiftForIdentity($userId, $normalizedEmail, $normalizedPhone);
            return false;
        }

        $this->maybeUnblockGiftForIdentity($userId, $normalizedEmail, $normalizedPhone);

        return true;
    }

    protected function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $trimmed = trim(mb_strtolower($email));

        return $trimmed !== '' ? $trimmed : null;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? $digits : null;
    }

    protected function hasOrdersWithPhone(string $normalizedPhone): bool
    {
        return Order::query()
            ->whereNotNull('phone')
            ->whereRaw($this->phoneNormalizeExpression() . ' = ?', [$normalizedPhone])
            ->exists();
    }

    protected function phoneNormalizeExpression(string $column = 'phone'): string
    {
        return sprintf(
            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(%s, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '')",
            $column
        );
    }

    protected function getMinOrderSum(): float
    {
        if ($this->minOrderSumCache !== null) {
            return $this->minOrderSumCache;
        }

        $setting = Setting::where('key', 'first_order_gift_min_order_sum')->first();
        $value = $setting?->value ?? 10000;

        return $this->minOrderSumCache = (float) $value;
    }

    protected function getGiftProduct(): ?Product
    {
        if ($this->giftProductCache) {
            return $this->giftProductCache;
        }

        $product = Product::query()
            ->where('is_first_order_gift', true)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->first();

        return $this->giftProductCache = $product;
    }

    protected function removeGift(?CartItem $giftItem): void
    {
        if ($giftItem && $this->isFirstOrderGiftCartItem($giftItem)) {
            $giftItem->delete();
        }
    }

    protected function resolveCartItemPrice(CartItem $item): float
    {
        $itemModel = $item->item;

        if (!$itemModel) {
            return 0.0;
        }

        $cityQuantity = $itemModel->city_quantity ?? 0;
        if ($cityQuantity <= 0) {
            return 0.0;
        }

        if ($itemModel instanceof CartItemPriceable) {
            return (float) $itemModel->getCartPrice();
        }

        return 0.0;
    }

    protected function isFirstOrderGiftCartItem(?CartItem $item): bool
    {
        if (!$item instanceof CartItem || !$item->is_gift) {
            return false;
        }

        $giftProduct = $this->getGiftProduct();
        if (!$giftProduct) {
            return false;
        }

        if ($item->item instanceof Product) {
            return (bool) $item->item->is_first_order_gift;
        }

        if ($item->item_type === CartItemTypeEnum::Product && (int) $item->item_id === (int) $giftProduct->id) {
            return true;
        }

        return false;
    }

    protected function hasExistingOrderForIdentity(?int $userId, ?string $normalizedEmail, ?string $normalizedPhone, ?User $fallbackUser = null): bool
    {
        if ($userId) {
            if (Order::where('user_id', $userId)->exists()) {
                return true;
            }
        } elseif ($fallbackUser instanceof User && Order::where('user_id', $fallbackUser->id)->exists()) {
            return true;
        }

        if ($normalizedEmail && Order::whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
            return true;
        }

        if ($normalizedPhone && $this->hasOrdersWithPhone($normalizedPhone)) {
            return true;
        }

        return false;
    }

    protected function blockGiftForIdentity(?int $userId, ?string $normalizedEmail, ?string $normalizedPhone): void
    {
        session()->put('first_order_gift_block', [
            'user_id' => $userId,
            'email' => $normalizedEmail,
            'phone' => $normalizedPhone,
        ]);
    }

    protected function maybeUnblockGiftForIdentity(?int $userId, ?string $normalizedEmail, ?string $normalizedPhone): void
    {
        $state = session('first_order_gift_block');
        if (!$state) {
            return;
        }

        if ($userId === null && $normalizedEmail === null && $normalizedPhone === null) {
            return;
        }

        if (!$this->isSameIdentity($state, $userId, $normalizedEmail, $normalizedPhone)) {
            session()->forget('first_order_gift_block');
        }
    }

    protected function isGiftBlockedForIdentity(?int $userId, ?string $normalizedEmail, ?string $normalizedPhone): bool
    {
        $state = session('first_order_gift_block');
        if (!$state) {
            return false;
        }

        if ($userId === null && $normalizedEmail === null && $normalizedPhone === null) {
            return true;
        }

        return $this->isSameIdentity($state, $userId, $normalizedEmail, $normalizedPhone);
    }

    protected function isSameIdentity(array $state, ?int $userId, ?string $normalizedEmail, ?string $normalizedPhone): bool
    {
        return ($state['user_id'] ?? null) === $userId
            && ($state['email'] ?? null) === $normalizedEmail
            && ($state['phone'] ?? null) === $normalizedPhone;
    }

    protected function isCollectionFirstOrderGiftItem($item): bool
    {
        if (empty($item) || empty($item->is_gift)) {
            return false;
        }

        $giftProduct = $this->getGiftProduct();
        if (!$giftProduct) {
            return false;
        }

        $resource = data_get($item, 'item');
        if ($resource instanceof ProductCartResource) {
            $resourceItem = $resource->resource ?? null;
            if ($resourceItem instanceof Product) {
                return (bool) $resourceItem->is_first_order_gift;
            }
        }

        $itemId = data_get($item, 'item_id');

        return (int) $itemId === (int) $giftProduct->id;
    }
}

