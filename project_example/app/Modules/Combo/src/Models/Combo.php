<?php

namespace Combo\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Product\Models\Product;
use Promotion\Models\Promotion;
use Review\Models\Review;
use App\Observers\ComboObserver;

#[ObservedBy([ComboObserver::class])]
class Combo extends Model implements CartItemPriceable
{
    use HasFactory, MultiFileable, HasTranslate;
    protected $fillable = [
        'uuid_1c',
        'name',
        'quantity',
        'article_number',
        'weight',
        'weight_type',
        'price',
        'price_type',
//        'sale_price',
        'description',
        'short_description',
        'seo_title',
        'seo_description',
        'keywords',
        'show_main',
    ];
    protected $casts = [
        'weight_type' => WeightTypeEnum::class,

    ];

    public function getQuantityAttribute(): int
    {
        return (int) ($this->attributes['quantity'] ?? 0);
    }

    public function getQuantityRawAttribute(): string
    {
        $quantity = $this->attributes['quantity'] ?? 0;

        if ($quantity === 0) {
            return 'Под заказ';
        }

        if ($quantity >= 50) {
            return 'Много';
        }

        return 'Мало';
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


    public function promotion()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_combo')->withTimestamps();
    }
    public function Globalpromotion()
    {
        return $this->belongsToMany(Globalpromotion::class, 'global_promotion_combo')->withTimestamps();
    }
    public function products()
    {
        // withoutPivot() - Laravel бы подгрузил ТОЛЬКО combo_id и product_id
        // withPivot('price') - Laravel ТАКЖЕ подгружает поле price из таблицы
        return $this->belongsToMany(Product::class, 'combo_product')
            ->withPivot('price');  // ← Загружает поле price в $product->pivot->price
    }
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'text'],
        ];
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'item');
    }

    public function getCityQuantityAttribute(): int
    {

        if ($this->quantity > 0){
            return 1;
        }

        return 0;
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
            // Исторически часть файлов могла сохраняться как 'firstImage', 'images' или 'image'
            ->whereIn('type_relation', ['images', 'firstImage', 'image'])
            // Показываем самое свежее изображение, чтобы обновления были видны сразу
            ->orderByDesc('id');
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



    // ЛОГИКА ЦЕН:

    //1) поле в апи ресурсах price - это цена по городам или стандартный price без уета скидок.
    //------НАЧАЛО---------
    public function getTotalPriceAttribute(): float
    {
        // Поле price всегда актуально (обновлено в afterSave при наличии переопределенных цен)
        return (float) ($this->attributes['price'] ?? 0);
    }
    //------КОНЕЦ---------

    //2) АКЦИОННАЯ ЦЕНА ТОВАРА ИСХОДЯ ИЗ АКЦИЙ В КОТОРЙ ЕСТЬ ТОВАР (ПОЛЕ sale_price):
    //------НАЧАЛО---------
    /**
     * Получает активную акцию для комбонабора
     */
    private function getActivePromotion(): ?Promotion
    {
        return $this->promotion()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();
    }

    public function getSalePriceAttribute(): ?float
    {
        $promotion = $this->getActivePromotion();

        if ($promotion && $promotion->sale_percent) {
            return round($this->total_price * (1 - $promotion->sale_percent / 100), 2);
        }

        return null;
    }

    public function getSalePercent(): int
    {
        $promotion = $this->getActivePromotion();

        return $promotion && $promotion->sale_percent ? (int) $promotion->sale_percent : 0;
    }

    //------КОНЕЦ---------

    //3) ЦЕНА ТОВАРА В КОРЗИНЕ:
    //------НАЧАЛО---------
    public function getCartPrice(): float
    {
        return $this->sale_price ?? $this->total_price;
    }
    //------КОНЕЦ---------

    // КОНЕЦ ЛОГИКИ ЦЕН:

    /**
     * Проверяет, привязана ли к комбонабору активная акция
     */
    public function hasActivePromotion(): bool
    {
        return $this->getActivePromotion() !== null;
    }

    /**
     * Scope: фильтрует комбонаборы, у которых есть хотя бы один привязанный товар
     */
    public function scopeHasProducts($query)
    {
        return $query->whereHas('products');
    }

}
