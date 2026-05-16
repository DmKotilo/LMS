<?php

namespace Catalog\Http\Resources;

use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Cart\src\Http\Resources\ProductCartResource;
use App\Modules\Cart\src\Services\CartFormattedItemsService;
use App\Modules\Combination\src\Http\Resources\CombinationCardResource;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Http\Resources\ReviewResource;
use Brand\Http\Resources\BrandResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Product\Models\Product;
use Setting\Models\Setting;

class ProductDetailResource extends JsonResource
{
    use GetCart;
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Product::class;
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $cart = $this->getCart($request);

        $approvedReviews = $this->reviews->where('status', ReviewStatusEnum::Approved);

        $videos = $this->videos->sortBy('position')->map(function ($video) {
            return [
                'id' => $video->id,
                'file_name' => $video->file_name,
                'video_url' => Storage::url($video->path),
                'position' => (int) $video->position,
                'preview' => $video->preview?->preview ? [
                    'id' => $video->preview->preview->id,
                    'url' => Storage::url($video->preview->preview->path),
                    'file_name' => $video->preview->preview->file_name,
                ] : null,
            ];
        })->values();
        $giftProductBlock = $this->formatGiftProductBlock();

        return [
            'id' => $this->id,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'count_in_cart' => $this->getCountInCart($cart),
            'is_new' => $this->is_new,
            'degree_type' => $this->degree_type,
            'article_number' => $this->article_number,
            'description' => $this->translate('description'),
            'short_description' => $this->translate('short_description'),
            'seo_description' => $this->seo_title,
            'seo_title' => $this->seo_description,
            'slug' => $this->slug,

            'weight' => ($this->is_weight_show && $this->weight > 0) ? $this->weight : null,
            'weight_type' => ($this->is_weight_show && $this->weight > 0) ? $this->weight_type?->toString() : null,
            'is_weight_show' => $this->is_weight_show,
            'quantity' => $this->quantity,
            'price' => $this->base_price,
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $this->base_sale_price,
            'sale_percent' => $this->getSalePercent(),
            'cashback_percent' => $this->getCashbackPercent(),
            'is_wishlist' => $this->isInWishlist($user),
            'reviews_count' => $approvedReviews->count(),
            'average_rating' => $this->rating,
            'manufacturer' => [
                'name' => $this->manufacturer?->name,
                'content' => $this->manufacturer?->content,
                'image' => $this->manufacturer ? new FileResource($this->manufacturer->image) : null,
            ],
            'images' => FileResource::collection($this->images),
            'brands' => BrandResource::collection($this->brands),
            'attributes' => ProductAttributeResource::collection($this->attributes),
            'tags' => TagResource::collection($this->tags),
            'reviews' => ReviewResource::collection($approvedReviews),
            'categories' => $this->getSelectedCategoryTree($request),
            'metrica_categories' => str_replace(' -> ', ' / ', (($this->resource instanceof Product && $this->resource->relationLoaded('selectedRootCategory'))
                ? ($this->resource->getRelation('selectedRootCategory')?->full_breadcrumb)
                : null) ?? $this->categories->sortBy('id')->first()?->full_breadcrumb ?? '') ?: null,
            'cutting' => [
              'content' => $this->cutting?->content,
              'button_link' => $this->cutting?->button_link,
              'button_text' => $this->cutting?->button_text,
              'video_link' => $this->cutting?->video_link,
              'image' => $this->cutting ? new FileResource($this->cutting->image) : null,
            ],

            'videos' => $videos,
            'is_have_gift' => $giftProductBlock !== null,
            'gift_product' => $giftProductBlock,
            'second_item_discount_percent' => $this->getSecondItemDiscountPercent(),
        ];
    }

    /**
     * Сформировать блок подарков в формате, аналогичном корзине.
     */
    protected function formatGiftProductBlock(): ?array
    {
        $giftProducts = $this->giftProducts()->where('is_active', true)->get();

        if ($giftProducts->isEmpty()) {
            return null;
        }

        $cityQuantity = $this->city_quantity ?? null;
        $isAvailable = $cityQuantity !== null ? $cityQuantity > 0 : true;

        $parentEntry = [
            'id' => $this->id,
            'quantity' => 1,
            'is_available' => $isAvailable,
            'city_quantity' => $cityQuantity,
            'total_price_without_sale' => $this->total_price ?? 0,
            'total_price' => $this->sale_price ?? $this->total_price ?? 0,
            'sale_percent' => $this->getSalePercent(),
            'is_combo' => false,
            'item' => new ProductCartResource($this),
            'item_type' => CartItemTypeEnum::Product,
            'is_gift' => false,
            'can_delete' => true,
            'gift_product_ids' => $giftProducts->pluck('id')->all(),
        ];

        $giftEntries = $giftProducts->map(function ($giftProduct) {
            $giftCityQuantity = $giftProduct->city_quantity ?? null;
            $giftIsAvailable = $giftCityQuantity !== null ? $giftCityQuantity > 0 : true;

            return [
                'id' => null,
                'quantity' => 1,
                'is_available' => $giftIsAvailable,
                'city_quantity' => $giftCityQuantity,
                'total_price_without_sale' => 0,
                'total_price' => 0,
                'sale_percent' => 0,
                'is_combo' => false,
                'item' => new ProductCartResource($giftProduct),
                'item_type' => CartItemTypeEnum::Product,
                'is_gift' => true,
                'can_delete' => false,
            ];
        });

        $formatted = CartFormattedItemsService::format(collect([$parentEntry])->merge($giftEntries));

        return $formatted[0] ?? null;
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

        return $product->categories
            ->contains(fn ($category) => $category->has_second_item_discount ?? false);
    }

    /**
     * Получить выбранное дерево категорий на основе query параметра category_id
     */
    protected function getSelectedCategoryTree(Request $request)
    {
        $product = $this->resource;

        if (!$product instanceof Product) {
            return CategoryResource::collection(collect());
        }

        // Проверяем, есть ли выбранная корневая категория, переданная через relation
        $selectedRootCategory = $product->relationLoaded('selectedRootCategory')
            ? $product->getRelation('selectedRootCategory')
            : null;

        if ($selectedRootCategory) {
            return CategoryResource::collection(collect([$selectedRootCategory]));
        }

        // Fallback: возвращаем все категории продукта (старое поведение)
        $product->loadMissing(['categories.parent']);
        return CategoryResource::collection($this->categories->sortBy('id')->values());
    }
}
