<?php

namespace Alert\Models;
use Alert\Enums\AlertTypeEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;


class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'title',
        'bonus_count',
        'days_before_expire',
        'bonus_message',
        'bonus_date',
        'message',
        'button_text',
        'button_link',
        'is_read',
        'user_deleted',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'type' => AlertTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
