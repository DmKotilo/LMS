<?php

namespace Product\Models;
use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\City\src\Models\CityProductCount;
use App\Modules\City\src\Models\CityProductPrice;
use App\Modules\Product\Product\src\Enums\DegreeTypeEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Product\Product\src\Models\AdminTag;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Traits\Localization\HasTranslate;
use Attribute\Models\Attribute;
use Attribute\Models\ProductAttribute;
use Brand\Models\Brand;
use Catalog\Http\Resources\ProductPreviewResource;
use Category\Models\Category;
use Category\Models\ProductCategory;
use Combination\Models\Combination;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
// удалён импорт WhitelistItem из пространства имён List из-за конфликтующего ключевого слова
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Manufacturer\Models\Manufacturer;
use Promotion\Models\Promotion;
use Promotion\Models\PromotionItem;
use Recipe\Models\RecipeVariant;
use Recipe\Models\SelectionRecipe;
use Review\Models\ProductReview;
use Review\Models\Review;
use Tag\Models\ProductTag;
use Tag\Models\Tag;
use App\Observers\ProductObserver;


#[ObservedBy([ProductObserver::class])]
class Product extends Model implements CartItemPriceable
{
    use HasFactory, MultiFileable, HasTranslate;

    protected $fillable = [
        'uuid_1c',
        'uuid_bitrix24',
        'quantity',
        'article_number',
        'weight',
        'weight_type',
        'name',
        'name_1c',
        'price',
        'price_per_kg',

        'price_type',
        'description',
        'description_1с',
        'category_1c',
        'subcategory_1c',
        'short_description',
        'seo_title',
        'keywords',
        'manufacturer_id',
        'preview_images',
        'seo_description',
        'slug',
        'is_active',
        'is_analog',
        'is_new',
        'degree_type',
        'is_first_order_gift',
        'is_weight_show',
    ];

    protected $casts = [
        'weight_type' => WeightTypeEnum::class,
        'preview_images' => 'array',
        'degree_type' => DegreeTypeEnum::class,
        'is_first_order_gift' => 'boolean',
        'is_analog' => 'boolean',
        'is_weight_show' => 'boolean',
    ];

/*    protected $appends = ['rating', 'totalPrice'];*/
    public function promotion()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product')->withTimestamps();
    }
    public function combinations()
    {
        return $this->hasMany(Combination::class);
    }

    public function getActivePromotionInfo(): ?array
    {
        $activePromotion = $this->promotion()
            ->where('promotions.is_active', true)
            ->where(function ($q) {
                $q->whereNull('promotions.end_date')
                    ->orWhere('promotions.end_date', '>=', now());
            })
            ->with(['cities' => function ($q) {
                $q->select('cities.id', 'cities.name');
            }])
            ->first();

        if ($activePromotion && $activePromotion->sale_percent) {
            return [
                'sale_percent' => (float) $activePromotion->sale_percent,
                'cities' => $activePromotion->cities,
            ];
        }

        return null;
    }

    public function Globalpromotion()
    {
        return $this->belongsToMany(GlobalPromotion::class, 'global_promotion_product')->withTimestamps();
    }

    public function getCashbackPercent(): ?float
    {
        $activePromotion = $this->Globalpromotion()
            ->where('is_active', true)
            ->where('type', \App\Modules\GlobalPromotion\src\Enums\GlobalPromotionTypeEnum::ProductCashback)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->whereNotNull('cashback_percent')
            ->orderByDesc('cashback_percent') // Берем максимальный процент, если несколько акций
            ->first();

        return $activePromotion ? (float) $activePromotion->cashback_percent : null;
    }
    public function getRatingAttribute(): int
    {
        $approvedReviews = $this->reviews()
            ->where('status', ReviewStatusEnum::Approved)
            ->get();

        $approvedCount = $approvedReviews->count();

        if ($approvedCount === 0) {
            return 0;
        }

        $averageRating = $approvedReviews->avg('rating');

        return (int) round($averageRating);
    }

    public function getTotalWeightGramAttribute() : float | int
    {
        if($this->weight_type == WeightTypeEnum::Kilo) {
            return (float) $this->weight * 1000;
        } else {
            return $this->weight;
        }
    }
    public function getTotalWeightKiloAttribute() : float | int
    {
        if($this->weight_type == WeightTypeEnum::Gram) {
            return (float) $this->weight / 1000;
        } else {
            return $this->weight;
        }
    }

    public function getPriceTypeAttribute(): ?PriceTypeEnum
    {
        $value = $this->attributes['price_type'] ?? null;

        if ($value === null) {
            return null;
        }

        // Поддержка старого значения 'руб/порция' для обратной совместимости
        if ($value === 'руб/кг' || $value === 'руб/порция') {
            return PriceTypeEnum::Per_Kilo;
        }

        if ($value === 'руб/шт') {
            return PriceTypeEnum::Per_Piece;
        }

        return PriceTypeEnum::tryFrom($value);
    }

    public function setPriceTypeAttribute(PriceTypeEnum|string|null $value): void
    {
        if ($value === null) {
            $this->attributes['price_type'] = null;
            return;
        }

        if ($value instanceof PriceTypeEnum) {
            $this->attributes['price_type'] = $value->value;
            return;
        }

        // Конвертируем 'руб/порция' в 'руб/кг' при сохранении
        if ($value === 'руб/порция') {
            $this->attributes['price_type'] = PriceTypeEnum::Per_Kilo->value;
            return;
        }

        $this->attributes['price_type'] = $value === 'руб/кг'
            ? PriceTypeEnum::Per_Kilo->value
            : $value;
    }


    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'long_text'],
            'short_description' => ['title'=>'Короткое описание', 'type'=>'textarea'],
        ];
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNotNull('price')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('is_analog')
                  ->orWhere('is_analog', false);
            })
            ->whereHas('categories');
    }

    /**
     * Проверяет, является ли товар активным (тот же критерий, что и scopeActive).
     * Неактивные товары должны вести на 404.
     */
    public function isActive(): bool
    {
        if ($this->price === null || !$this->is_active) {
            return false;
        }
        if ($this->is_analog === true) {
            return false;
        }
        return $this->categories()->exists();
    }

    public function images()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'images')
            ->orderByRaw('COALESCE(position, 0), id');
    }
    public function firstImage()
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type_relation', 'images')
            ->orderByRaw('COALESCE(position, 0), id');
    }



    public function videos()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'videos')
            ->orderByRaw('COALESCE(position, 0), id');
    }
//    public function makeFile($file)
//    {
//        return $file;
//    }

//    public function selectFileRelation(string $relation)
//    {
//        return $this->$relation();
//    }
    public function firstVideo()
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type_relation', 'videos')
            ->orderByRaw('COALESCE(position, 0), id');
    }
    public function videoPreviews()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'video_previews');
    }
    public function videosWithPreviews()
    {
        return $this->videos->map(function ($video) {
            return [
                'video' => $video,
                'preview' => $video->preview?->preview,
            ];
        });
    }


    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('value', 'explanation');
    }

    public function attributes_values()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brands');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function product_categories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function productСategories() //для МУНШАЙНА relatedLink
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }
    public function productTags()
    {
        return $this->hasMany(ProductTag::class);
    }

    public function adminTags()
    {
        return $this->belongsToMany(AdminTag::class, 'products_admin_tags');
    }



    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }


    public function reviews()
    {
        return $this->morphMany(Review::class, 'item');
    }

    public function variants(): MorphToMany
    {
        return $this->morphToMany(RecipeVariant::class, 'recipe_variantable');
    }

    public function inWhitelist(): MorphOne
    {
        return $this->morphOne('List\\Models\\WhitelistItem', 'item');
    }
    public function inCart(): MorphOne
    {
        return $this->morphOne(CartItem::class, 'item');
    }
    public function relatedProducts()
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        );
    }
    public function autoRelatedProducts()
    {
        return $this->belongsToMany(
            Product::class,
            'auto_related_products',
            'product_id',
            'related_product_id'
        )->withPivot([
            'joint_orders_count',
            'product_orders_count',
            'confidence',
            'created_at',
            'updated_at',
        ])->withTimestamps();
    }
    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'combo_product');
    }

    public function giftProducts()
    {
        return $this->belongsToMany(Product::class, 'product_gifts', 'product_id', 'gift_product_id')
            ->withTimestamps();
    }

    public function productsWithGift()
    {
        return $this->belongsToMany(Product::class, 'product_gifts', 'gift_product_id', 'product_id')
            ->withTimestamps();
    }

    public function cutting(){
        return $this->hasOne(CuttingProduct::class);
    }


    public function getCountInCart($cart)
    {
        return (int) $cart->items()
            ->where('item_type', self::class)
            ->where('item_id', $this->id)
            ->value('quantity') ?? 0;
    }
    public function isInWishlist($user)
    {
        if ($user && $user->whiteList) {
            return $user->whiteList->items()
                ->where('item_type', self::class)
                ->where('item_id', $this->id)
                ->exists();
        }
        return false;
    }
    public function getDisplayImages()
    {
        return $this->preview_images
            ? ProductPreviewResource::collection(collect($this->preview_images))
            : FileResource::collection($this->images);
    }
    public function cityCounts()
    {
        return $this->hasMany(CityProductCount::class);
    }

    public function getQuantityAttribute(): string
    {
        $city = session('user_city');

        if ($city && isset($city['city_id'])) {

            $cityQuantity = $this->cityCounts()
                ->where('city_id', $city['city_id'])
                ->value('quantity');

            if (!empty($city['city']) && mb_strtolower($city['city']) === mb_strtolower('Другой город')) {
                return 'Мало';
            }

            if($cityQuantity === 0  || $cityQuantity == null) return 'Под заказ';
            return ($cityQuantity !== null && $cityQuantity > 50)
                ? 'Много'
                : 'Мало';
        }

        return 'Мало';
    }

    // берем количество в городе и если оно !null или больше 0 то товар доступен к покупке и учитывается в корзине!!!!
    public function getCityQuantityAttribute(): int
    {
        $city = session('user_city');

        if ($city && isset($city['city_id'])) {
            return (int) $this->cityCounts()
                ->where('city_id', $city['city_id'])
                ->value('quantity') ?? 0;
        }

        return 0;
    }








    // ЛОГИКА ЦЕН:

    //1) БАЗОВАЯ ЦЕНА (для карточек и деталки товара) - исходная цена БЕЗ умножения на вес
    //------НАЧАЛО---------
    /**
     * Получить базовую цену (для карточек и деталки товара)
     * Возвращает исходную цену за кг БЕЗ умножения на вес
     */
    public function getBasePriceAttribute(): float
    {
        return $this->baseCityPrice();
    }

    /**
     * Получить базовую цену по городу (исходная цена БЕЗ умножения)
     */
    public function basePriceInCity($cityId): float
    {
        $cityPriceRecord = $this->cityPrices()
            ->where('city_id', $cityId)
            ->first();

        if ($cityPriceRecord) {
            // Возвращаем исходную цену из БД (не умноженную)
            return $cityPriceRecord->price ?? 0;
        }

        // Если нет цены по городу, используем базовую цену продукта
        return $this->price ?? 0;
    }

    /**
     * Получить базовую цену с учетом города (для карточек/деталки)
     */
    protected function baseCityPrice(): float
    {
        $city = session('user_city');

        if ($city && isset($city['city_id'])) {
            return $this->basePriceInCity($city['city_id']);
        }

        // Возвращаем исходную цену (не умноженную)
        return $this->price ?? 0;
    }
    //------КОНЕЦ---------

    //2) ЦЕНА ДЛЯ КОРЗИНЫ И ЗАКАЗА (с умножением на вес для руб/кг)
    //------НАЧАЛО---------
    /**
     * Получить цену для корзины и заказа (с умножением на вес для руб/кг)
     * Используется в корзине и при оформлении заказа
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->cityPrice();
    }

    public function cityPrices()
    {
        return $this->hasMany(CityProductPrice::class);
    }

    /**
     * Получить цену по городу для корзины/заказа (с умножением)
     */
    public function priceInCity($cityId): float
    {
        $cityPriceRecord = $this->cityPrices()
            ->where('city_id', $cityId)
            ->first();

        $usePerKg = $this->shouldRecalculatePricePerKg();

        if ($cityPriceRecord) {
            // Для руб/кг используем price_per_kg, иначе обычную цену
            if ($usePerKg) {
                return $cityPriceRecord->price_per_kg ?? $cityPriceRecord->price ?? 0;
            }

            return $cityPriceRecord->price ?? 0;
        }

        // Если нет цены по городу, используем базовую цену продукта (с умножением если нужно)
        if ($usePerKg) {
            return $this->price_per_kg ?? $this->price ?? 0;
        }

        return $this->price ?? 0;
    }

    /**
     * Получить цену с учетом города для корзины/заказа (с умножением)
     */
    protected function cityPrice(): float
    {
        $city = session('user_city');

        if ($city && isset($city['city_id'])) {
            return $this->priceInCity($city['city_id']);
        }

        // Используем price_per_kg если есть (умноженная цена), иначе price
        return $this->shouldRecalculatePricePerKg()
            ? ($this->price_per_kg ?? $this->price ?? 0)
            : ($this->price ?? 0);
    }
    //------КОНЕЦ---------

    //2) АКЦИОННАЯ ЦЕНА ТОВАРА ИСХОДЯ ИЗ АКЦИЙ В КОТОРЙ ЕСТЬ ТОВАР (ПОЛЕ sale_price):
    //------НАЧАЛО---------
    /**
     * Переопределение sale_price для скидки на второй товар
     * Если установлено, то аксессор вернет это значение вместо цены из акций
     */
    public ?float $second_item_sale_price_override = null;

    /**
     * Процент скидки на второй товар (устанавливается извне)
     */
    public ?int $second_item_sale_percent_override = null;

    /**
     * Акционная цена для корзины и заказа (на основе total_price с умножением)
     */
    public function getSalePriceAttribute(): ?float
    {
        // Если установлена скидка на второй товар, используем её
        if ($this->second_item_sale_price_override !== null) {
            return $this->second_item_sale_price_override;
        }

        // Иначе ищем обычную акцию
        $promotion = $this->promotion()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->when(session('user_city.city_id'), function ($q, $cityId) {
                $q->whereHas('cities', function ($cq) use ($cityId) {
                    $cq->where('cities.id', $cityId);
                });
            })
            ->first();

        if ($promotion && $promotion->sale_percent) {
            return round($this->total_price * (1 - $promotion->sale_percent / 100), 2);
        }

        return null;
    }

    /**
     * Акционная цена для карточек и деталки товара (на основе base_price БЕЗ умножения)
     */
    public function getBaseSalePriceAttribute(): ?float
    {
        // Если установлена скидка на второй товар, используем её (но на основе base_price)
        if ($this->second_item_sale_price_override !== null) {
            // Пересчитываем скидку на основе base_price
            $basePrice = $this->base_price;
            $totalPrice = $this->total_price;
            if ($totalPrice > 0 && $basePrice > 0) {
                $discountRatio = $this->second_item_sale_price_override / $totalPrice;
                return round($basePrice * $discountRatio, 2);
            }
            return $this->second_item_sale_price_override;
        }

        // Иначе ищем обычную акцию
        $promotion = $this->promotion()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->when(session('user_city.city_id'), function ($q, $cityId) {
                $q->whereHas('cities', function ($cq) use ($cityId) {
                    $cq->where('cities.id', $cityId);
                });
            })
            ->first();

        if ($promotion && $promotion->sale_percent) {
            return round($this->base_price * (1 - $promotion->sale_percent / 100), 2);
        }

        return null;
    }
    //------КОНЕЦ---------

    public function getSalePercent(): int
    {
        // Если установлен процент скидки на второй товар, возвращаем его
        if ($this->second_item_sale_percent_override !== null) {
            return $this->second_item_sale_percent_override;
        }

        // Иначе ищем обычную акцию
        $promotion = $this->promotion()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->when(session('user_city.city_id'), function ($q, $cityId) {
                $q->whereHas('cities', function ($cq) use ($cityId) {
                    $cq->where('cities.id', $cityId);
                });
            })
            ->first();

        if ($promotion && $promotion->sale_percent) {
            return (int) $promotion->sale_percent;
        }

        return 0;
    }

    //3) ЦЕНА ТОВАРА В КОРЗИНЕ:
    //------НАЧАЛО---------
    public function getCartPrice(): float
    {
        return $this->sale_price ?? $this->total_price;
    }
    //------КОНЕЦ---------
    // КОНЕЦ ЛОГИКИ ЦЕН:
    public function recalculatePricePerKg(): void
    {
        if (! $this->shouldRecalculatePricePerKg()) {
            return;
        }

        $pricePerKg = null;

        if ($this->price && $this->weight) {
            $weightInKilos = $this->getWeightInKilosForPrice();
            $pricePerKg = round($this->price * $weightInKilos, 2);
        }

        DB::table('products')
            ->where('id', $this->id)
            ->update(['price_per_kg' => $pricePerKg]);

        if ($this->weight) {
            $weightInKilos = $this->getWeightInKilosForPrice();
            $cityPrices = DB::table('city_product_prices')
                ->where('product_id', $this->id)
                ->get();

            foreach ($cityPrices as $cityPrice) {
                $cityPricePerKg = null;
                if ($cityPrice->price) {
                    $cityPricePerKg = round($cityPrice->price * $weightInKilos, 2);
                }

                DB::table('city_product_prices')
                    ->where('id', $cityPrice->id)
                    ->update(['price_per_kg' => $cityPricePerKg]);
            }
        }
    }

    public function shouldRecalculatePricePerKg(): bool
    {
        $rawPriceType = $this->getRawOriginal('price_type');
        $rawPriceType = is_string($rawPriceType) ? trim($rawPriceType) : $rawPriceType;

        return $rawPriceType === PriceTypeEnum::Per_Kilo->value
            || $rawPriceType === 'руб/порция';
    }

    protected function getWeightInKilosForPrice(): float
    {
        $weightType = $this->getRawOriginal('weight_type');
        $weightType = is_string($weightType) ? trim($weightType) : $weightType;

        if ($weightType === WeightTypeEnum::Gram->value) {
            return (float) $this->weight / 1000;
        }

        return (float) $this->weight;
    }
}
