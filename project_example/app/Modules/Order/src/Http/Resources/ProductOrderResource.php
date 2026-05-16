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
use Product\Models\Product;
use Setting\Models\Setting;

class ProductOrderResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Product::class;

    protected array $orderItemData = [];

    public function __construct($resource, array $orderItemData = [])
    {
        parent::__construct($resource);
        $this->orderItemData = $orderItemData;
    }

    public function toArray(Request $request): array
    {
        $slug = 'tip-upakovki';
        $attribute = $this->attributes->where('slug','tip-upakovki');
        if ($attribute){
            $type_of_packing = $this->attributes_values()?->whereHas('attribute', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })->value('value');
        }else{
            $type_of_packing = '-';
        }

        // Используем сохраненные значения из OrderItem, если они доступны
        $price = $this->orderItemData['order_item_price_without_sale'] ?? $this->total_price;
        $salePrice = $this->orderItemData['order_item_price'] ?? $this->sale_price;
        $salePercent = $this->orderItemData['order_item_sale_percent'] ?? $this->getSalePercent();

        return [
            'id' => $this->id,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'article_number' => $this->article_number,
            'weight' => ($this->is_weight_show && $this->weight > 0) ? round($this->weight, 2) : null,
            'weight_type' => ($this->is_weight_show && $this->weight > 0) ? $this->weight_type?->toString() : null,
            'is_weight_show' => $this->is_weight_show,
            'price' => round($price, 2), // Цена за единицу без скидки (из заказа)
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $salePrice ? round($salePrice, 2) : null, // Цена за единицу со скидкой (из заказа)
            'cashback_percent' => $this->getCashbackPercent(),
            'images' => $this->getDisplayImages(),
            'attributes' => ProductAttributeResource::collection($this->attributes_values),
            'brands' => BrandResource::collection($this->brands),
            'slug' => $this->slug,
            'second_item_discount_percent' => $this->getSecondItemDiscountPercent(),
            // Дополнительные поля для обратной совместимости
            'article' => $this->article_number,
            'type_of_packing' => $type_of_packing,
        ];
    }

    protected static ?int $secondItemPercentCache = null;

    protected function getSecondItemDiscountPercent(): ?int
    {
        if (!$this->hasSecondItemDiscountCategory()) {
            return null;
        }

        if (is_null(self::$secondItemPercentCache)) {
            $value = Setting::where('key', 'second_item_discount_percent')->value('value');
            self::$secondItemPercentCache = is_null($value) ? 10 : (int) $value;
        }

        return self::$secondItemPercentCache;
    }

    protected function hasSecondItemDiscountCategory(): bool
    {
        $product = $this->resource;

        if (!$product instanceof Product) {
            return false;
        }

        $product->loadMissing('categories');

        return (bool) $product->categories
            ->first(fn ($category) => $category->has_second_item_discount ?? false);
    }
}
