<?php

namespace GlobalPromotion\Models;
use App\Modules\GlobalPromotion\src\Enums\GlobalPromotionTypeEnum;
use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use App\Traits\Localization\HasTranslate;
use Banner\Models\Banner;
use City\Models\City;
use Combo\Models\Combo;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class GlobalPromotion extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'bonus_points',
        'min_order_sum_for_bonus',
        'max_order_sum_for_bonus',
        'cashback_percent',
        'min_order_sum_for_cashback',
        'type',
        'is_active'
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'type' => GlobalPromotionTypeEnum::class,
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'global_promotion_product');
    }

    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'global_promotion_combo');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class, 'global_promotion_id');
    }



}
