<?php

namespace Review\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Models\ReviewAnswer;
use App\Modules\Review\src\Models\ReviewReaction;
use App\Traits\Localization\HasTranslate;
use Cart\Models\UserCart;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;
use Review\Observers\ReviewObserver;
use User\Models\User;
#[ObservedBy([ReviewObserver::class])]
class Review extends Model
{
    use HasFactory, MultiFileable;

    protected $fillable = [
        'user_id',
        'text',
        'rating',
        'status',
        'item_type',
        'item_id',
        'show_main',
        'show_catalog',
        'show_about_us',
        'show_promotion_page',
    ];

    protected $casts = [
        'status' => ReviewStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function answer()
    {
        return $this->hasOne(ReviewAnswer::class);
    }

    public function images()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'images')
            ->orderByRaw('COALESCE(position, 0), id');
    }

    public function reactions()
    {
        return $this->hasMany(ReviewReaction::class);
    }

    public function likes()
    {
        return $this->reactions()->where('type', 'like');
    }

    public function dislikes()
    {
        return $this->reactions()->where('type', 'dislike');
    }

    public function likedByUser($userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function dislikedByUser($userId): bool
    {
        return $this->dislikes()->where('user_id', $userId)->exists();
    }

    public function likesCount(): int
    {
        return $this->likes()->count();
    }

    public function dislikesCount(): int
    {
        return $this->dislikes()->count();
    }

    public function setItemTypeAttribute($value)
    {
        if ($this->attributes['item_type'] ?? null) {
            return;
        }
        $this->attributes['item_type'] = $value;
    }
}
