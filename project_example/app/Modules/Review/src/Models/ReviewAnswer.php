<?php

namespace App\Modules\Review\src\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Traits\Localization\HasTranslate;
use Cart\Models\UserCart;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Models\MoonshineUser;
use Product\Models\Product;
use Recipe\Models\RecipeReview;
use Review\Models\Review;
use Review\Models\ReviewRating;
use User\Models\User;

class ReviewAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'review_id',
        'text',
        'is_active'
    ];
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function admin(){
        return $this->belongsTo(MoonshineUser::class);
    }


}
