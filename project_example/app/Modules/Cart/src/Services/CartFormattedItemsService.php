<?php

namespace App\Modules\Cart\src\Services;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonSerializable;

class CartFormattedItemsService
{
    /**
     * Группируем товары корзины и подарки по родителям.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function format(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $normalized = $items->map(fn ($item) => self::normalizeCartItem($item));

        $parents = [];
        $giftPool = [];

        foreach ($normalized as $item) {
            $isGift = (bool) data_get($item, 'is_gift', false);

            if ($isGift) {
                $productId = data_get($item, 'item.id');
                $giftPool[$productId ?: spl_object_id((object) $item)][] = $item;
                continue;
            }

            $parents[] = $item;
        }

        $formatted = [];

        foreach ($parents as $parent) {
            $giftItems = [];
            $giftIds = array_filter((array) data_get($parent, 'gift_product_ids', []));

            foreach ($giftIds as $giftId) {
                if (!empty($giftPool[$giftId])) {
                    $giftItems[] = self::cleanupHelperFields(array_shift($giftPool[$giftId]));
                }
            }

            $formatted[] = array_merge(
                self::cleanupHelperFields($parent),
                ['gift_items' => $giftItems]
            );
        }

        // Добавляем оставшиеся подарки (например, подарок за первый заказ)
        foreach ($giftPool as $gifts) {
            foreach ($gifts as $gift) {
                $formatted[] = array_merge(
                    self::cleanupHelperFields($gift),
                    ['gift_items' => []]
                );
            }
        }

        return $formatted;
    }

    protected static function normalizeCartItem($item): array
    {
        $item = self::normalizeValue($item);

        if (!is_array($item)) {
            return [];
        }

        if (isset($item['item'])) {
            $item['item'] = self::normalizeValue($item['item']);
        }

        return $item;
    }

    protected static function normalizeValue($value)
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        if ($value instanceof Collection) {
            return $value->map(fn ($item) => self::normalizeValue($item))->all();
        }

        if (is_object($value)) {
            return collect(get_object_vars($value))
                ->map(fn ($item) => self::normalizeValue($item))
                ->all();
        }

        return $value;
    }

    protected static function cleanupHelperFields(array $item): array
    {
        return Arr::except($item, ['gift_product_ids']);
    }
}

