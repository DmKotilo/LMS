<?php

namespace App\Modules\Review\src\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Models\ReviewAnswer;
use App\Traits\Localization\HasTranslate;
use Cart\Models\UserCart;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;
use Review\Models\Review;
use User\Models\User;

class ReviewReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }


}
