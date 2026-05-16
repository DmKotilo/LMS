<?php

namespace Referral\Models;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Modules\Referral\src\Enums\ReferralBonusTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;


class ReferralBonusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'user_id',
        'amount',
        'type',
    ];

    protected $casts = [
        'type' => ReferralBonusTypeEnum::class,
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }//связь с "рефералкой" по которой начислен бонус

    public function user()
    {
        return $this->belongsTo(\User\Models\User::class);
    } // кому начислен бонус

}
