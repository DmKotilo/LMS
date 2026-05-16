<?php

namespace Bonus\Models;
use App\Modules\Bonus\Bonus\src\Enums\BonusManualReasonEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Observers\BonusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MoonShine\Laravel\Models\MoonshineUser;
use User\Models\User;

#[ObservedBy([BonusObserver::class])]
class UserBonusHistory extends Model
{
    use HasFactory;
    protected $table = 'user_bonus_history';

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'remaining_amount',
        'type',
        'status',
        'active_date',
        'expires_at',
        'bonus_card_id',

        'is_manual',
        'reason',
        'admin_id',
        'comment',
    ];

    protected $casts = [
      'type' => BonusTypeEnum::class,
      'status' => BonusStatusEnum::class,

      'is_manual' => 'boolean',
      'reason' => BonusManualReasonEnum::class,

      'active_date' => 'datetime',
      'expires_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function card(){
        return $this->belongsTo(BonusCard::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Order\Models\Order::class);
    }
    public function admin(){
        return $this->belongsTo(MoonshineUser::class);
    }

    /**
     * Списания, в которых участвовало это начисление (если это accrual)
     */
    public function allocations()
    {
        return $this->hasMany(UserBonusAllocation::class, 'accrual_id');
    }

    /**
     * Начисления, из которых было сформировано это списание (если это withdrawal)
     */
    public function sources()
    {
        return $this->hasMany(UserBonusAllocation::class, 'withdrawal_id');
    }

    /**
     * Получить округленное вниз количество бонусов
     */
    public function getAmountFlooredAttribute(): ?int
    {
        return $this->amount !== null ? (int) floor((float) $this->amount) : null;
    }

    /**
     * Получить округленный вниз остаток бонусов
     */
    public function getRemainingAmountFlooredAttribute(): ?int
    {
        return $this->remaining_amount !== null ? (int) floor((float) $this->remaining_amount) : null;
    }
}
