<?php

namespace Bonus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBonusAllocation extends Model
{
    use HasFactory;
    
    protected $table = 'user_bonus_allocations';

    protected $fillable = [
        'withdrawal_id',
        'accrual_id',
        'amount',
    ];

    /**
     * Запись списания
     */
    public function withdrawal()
    {
        return $this->belongsTo(UserBonusHistory::class, 'withdrawal_id');
    }

    /**
     * Запись начисления
     */
    public function accrual()
    {
        return $this->belongsTo(UserBonusHistory::class, 'accrual_id');
    }
}

