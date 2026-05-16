<?php

namespace Notification\Models;
use Alert\Enums\AlertTypeEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Notification\Enums\NotificationTypeEnum;
use Product\Models\Product;
use Promotion\Models\Promotion;
use User\Models\User;


class PromoNotification extends Model
{
    protected $fillable = [
        'user_id',
        'promotion_id',
        'product_id',
        'type',
        'is_read',
        'user_deleted',
        'active_date'
    ];
    protected $casts = [
      'type' => NotificationTypeEnum::class,
      'active_date' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope для фильтрации активных уведомлений (где active_date <= now())
     */
    public function scopeActive($query)
    {
        return $query->where('active_date', '<=', now());
    }
}

