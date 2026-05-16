<?php

namespace User\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Authorization\Models\PasswordReset;
use Authorization\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Loyalty\Models\LoyaltyLevel;
use User\Enums\RoleEnum;


class UserLoyaltyLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loyalty_level_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loyaltyLevel()
    {
        return $this->belongsTo(LoyaltyLevel::class);
    }
}
