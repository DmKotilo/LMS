<?php

namespace Bonus\Models;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;


class BonusCard extends Model
{
    use HasFactory;
    protected $table = 'bonus_cards';

    protected $fillable = [
        'user_id',
        'is_active',
        'number',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }


}
