<?php

namespace Referral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use User\Models\User;


class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'inviter_id',
        'invited_id',
        'referral_code',
        'registered_at',
        'first_purchase_at',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id'); //пиргласивший
    }

    public function invited()
    {
        return $this->belongsTo(User::class, 'invited_id'); // приглашенный
    }

    public function bonuses()
    {
        return $this->hasMany(ReferralBonusHistory::class);
    }


}
