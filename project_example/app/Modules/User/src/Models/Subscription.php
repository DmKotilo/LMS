<?php

namespace User\Models;

use Address\Model\Address;
use App\Modules\User\src\Enums\DeleteReasonEnum;
use Bonus\Models\UserBonusHistory;
use Bonus\Observers\BonusObserver;
use File\Models\Files\File;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
use Illuminate\Validation\Rule;
use List\Models\UserWhitelist;
use Loyalty\Models\LoyaltyLevel;
use Order\Models\Order;
use User\Enums\GenderEnum;
use User\Enums\RoleEnum;
use User\Models\User;
use User\Models\UserLoyaltyLevel;
use User\Observers\SubscriptionObserver;
use UserSetting\Models\UserSetting;
use Whitelist\Models\CallContactForm;

#[ObservedBy([SubscriptionObserver::class])]

class Subscription extends Model
{
    protected $fillable = ['email', 'user_id', 'is_active','name', 'is_bonus_expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
