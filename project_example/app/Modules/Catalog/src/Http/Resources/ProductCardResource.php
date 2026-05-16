<?php

namespace Catalog\Http\Resources;

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
use Illuminate\Support\Facades\Auth;
use Product\Models\Product;

class ProductCardResource extends JsonResource
{
    use GetCart;
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Product::class;

    public function paginationInformation($request, $paginated, $default)
    {
        $default['links']['custom'] = 'https://example.com';

        return $default;
    }
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $cart = $this->getCart($request);

        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'article_number' => $this->article_number,
            'name' => html_entity_decode($this->translate('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'keywords' => $this->keywords,
            'metrica_categories' => $this->getMetricaCategories(),
            'count_in_cart' => $this->getCountInCart($cart),
            'is_new' => $this->is_new,
            'seo_description' => $this->seo_description,
            'seo_title' => $this->seo_title,
            'slug' => $this->slug,
            'weight' => ($this->is_weight_show && $this->weight > 0) ? $this->weight : null,
            'weight_type' => ($this->is_weight_show && $this->weight > 0) ? $this->weight_type?->toString() : null,
            'is_weight_show' => $this->is_weight_show,
            'quantity' => $this->quantity,
            'degree_type' => $this->degree_type,
            'price' => $this->base_price,
            'sale_percent' => $this->getSalePercent(),
            'price_type' => $this->price_type?->toString(),
            'sale_price' => $this->base_sale_price,
            'cashback_percent' => $this->getCashbackPercent(),
            'is_wishlist' => $this->isInWishlist($user),
            'category_group_name' => $this->category_group_name,
            'is_have_gift' => $this->hasActiveGiftProducts(),
            'images' => $this->getDisplayImages(),
//            'attributes' => ProductAttributeResource::collection($this->attributes),
            'tags' => TagResource::collection($this->tags),
            'brands' => BrandResource::collection($this->brands),
        ];
    }

    protected function getMetricaCategories(): ?string
    {
        $product = $this->resource;

        if (!($product instanceof Product)) {
            return null;
        }

        $selectedRootCategory = $product->relationLoaded('selectedRootCategory')
            ? $product->getRelation('selectedRootCategory')
            : null;
        if ($selectedRootCategory instanceof Category) {
            return $this->buildCategoryPathString($selectedRootCategory);
        }

        $categories = $this->getProductCategoriesCollection($product);
        $category = $categories->sortBy('id')->first();

        return $category instanceof Category
            ? $this->buildCategoryPathString($category)
            : null;
    }

    /**
     * Возвращает категории продукта, используя уже загруженные отношения, если они есть.
     */
    protected function getProductCategoriesCollection(Product $product)
    {
        if ($product->relationLoaded('categories')) {
            return $product->categories;
        }

        if ($product->relationLoaded('product_categories')) {
            return $product->product_categories
                ->pluck('category')
                ->filter()
                ->values();
        }

        $product->loadMissing(['categories.parent']);
        return $product->categories;
    }

    protected function buildCategoryPathString(Category $category): string
    {
        $segments = [];
        $current = $category;

        while ($current) {
            $segments[] = (string) $current->name;
            $current->loadMissing('parent');
            $current = $current->parent;
        }

        $segments = array_reverse(array_filter($segments, static fn ($v) => $v !== ''));

        return implode(' / ', $segments);
    }

    protected function hasActiveGiftProducts(): bool
    {
        $product = $this->resource;

        if (!$product instanceof Product) {
            return false;
        }

        if ($product->relationLoaded('giftProducts')) {
            return (bool) $product->giftProducts
                ?->first(fn ($giftProduct) => (bool) ($giftProduct->is_active ?? false));
        }

        return $product->giftProducts()
            ->where('is_active', true)
            ->exists();
    }
}
