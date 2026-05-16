<?php

namespace Product\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Order\Models\OrderItem;
use Product\Models\AutoRelatedProduct;
use Product\Models\Product;

class AutoRelatedProductsService
{
    public function __construct(
        protected OrderItem $orderItem,
    ) {
    }

    public function rebuild(?Product $product = null): void
    {
        if ($product) {
            $this->rebuildForProduct($product);
            return;
        }

        $productIds = $this->baseQuery()
            ->select('item_id')
            ->distinct()
            ->pluck('item_id');

        Product::query()
            ->whereIn('id', $productIds)
            ->chunkById(50, function ($products) {
                $products->each(fn(Product $product) => $this->rebuildForProduct($product));
            });
    }

    public function rebuildForProduct(Product $product): void
    {
        $config = config('recommendations.auto_related');
        $orderIds = $this->baseQuery()
            ->where('item_id', $product->getKey())
            ->pluck('order_id')
            ->unique();

        $ordersCount = $orderIds->count();

        AutoRelatedProduct::query()
            ->where('product_id', $product->getKey())
            ->delete();

        if ($ordersCount < ($config['min_product_orders'] ?? 5)) {
            return;
        }

        $coItems = $this->orderItem
            ->newQuery()
            ->whereIn('order_id', $orderIds)
            ->where('item_type', Product::class)
            ->where('item_id', '!=', $product->getKey())
            ->get(['order_id', 'item_id'])
            ->groupBy('order_id');

        $jointCounts = $this->buildJointCounts($coItems);

        $minConfidence = $config['min_confidence'] ?? 0.3;

        $recommendations = collect($jointCounts)
            ->map(function (int $count, int $relatedProductId) use ($product, $ordersCount) {
                return [
                    'product_id' => $product->getKey(),
                    'related_product_id' => $relatedProductId,
                    'joint_orders_count' => $count,
                    'product_orders_count' => $ordersCount,
                    'confidence' => $ordersCount > 0 ? $count / $ordersCount : 0,
                ];
            })
            ->filter(fn(array $data) => $data['confidence'] >= $minConfidence)
            ->sort(function (array $first, array $second) {
                return $second['confidence'] <=> $first['confidence']
                    ?: $second['joint_orders_count'] <=> $first['joint_orders_count'];
            })
            ->take($config['limit'] ?? 10)
            ->values();

        if ($recommendations->isEmpty()) {
            return;
        }

        $timestamp = now();
        DB::table('auto_related_products')->insert(
            $recommendations
                ->map(fn(array $row) => $row + [
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->toArray()
        );
    }

    protected function baseQuery(): Builder
    {
        $config = config('recommendations.auto_related');
        $periodMonths = max((int)($config['period_months'] ?? 3), 1);
        $fromDate = now()->subMonths($periodMonths);
        $statuses = $config['eligible_statuses'] ?? [];

        return $this->orderItem
            ->newQuery()
            ->where('item_type', Product::class)
            ->whereHas('order', function (Builder $query) use ($fromDate, $statuses) {
                $query->where('created_at', '>=', $fromDate);
                if (!empty($statuses)) {
                    $query->whereIn('status', $statuses);
                }
            });
    }

    /**
     * @param  Collection<int, Collection<int, \Illuminate\Database\Eloquent\Model>>  $groupedItems
     */
    protected function buildJointCounts(Collection $groupedItems): array
    {
        $jointCounts = [];

        foreach ($groupedItems as $itemsInOrder) {
            $uniqueProducts = $itemsInOrder->pluck('item_id')->unique();

            foreach ($uniqueProducts as $relatedProductId) {
                $jointCounts[$relatedProductId] = ($jointCounts[$relatedProductId] ?? 0) + 1;
            }
        }

        return $jointCounts;
    }
}

