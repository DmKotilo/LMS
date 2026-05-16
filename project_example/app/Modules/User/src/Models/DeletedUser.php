<?php

namespace App\Modules\User\src\Models;

use Address\Model\Address;
use App\Modules\User\src\Enums\DeleteReasonEnum;
use Bonus\Models\UserBonusHistory;
use File\Models\Files\File;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
use Illuminate\Validation\Rule;
use List\Models\UserWhitelist;
use Loyalty\Models\LoyaltyLevel;
use Order\Models\Order;
use User\Enums\GenderEnum;
use User\Enums\RoleEnum;
use User\Models\User;
use User\Models\UserLoyaltyLevel;
use UserSetting\Models\UserSetting;
use Whitelist\Models\CallContactForm;


class DeletedUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'user_id',
        'reason',
        'comment',
    ];


    protected $casts = [
        'reason' => DeleteReasonEnum::class,
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }




}
