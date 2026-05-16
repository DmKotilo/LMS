<?php

namespace User\Models;

use Address\Model\Address;
use Address\Model\ContactMethod;
use Alert\Models\Alert;
use Bonus\Models\BonusCard;
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
use Notification\Models\PromoNotification;
use Order\Models\Order;
use User\Enums\GenderEnum;
use User\Enums\RoleEnum;
use UserSetting\Models\UserSetting;
use Whitelist\Models\CallContactForm;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'name',
        'second_name',
        'last_name',
        'gender',
        'vk_id',
        'yandex_id',
        'birth_date',
        'phone',
        'new_phone',
        'email',
        'new_email',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'is_self_deleted',
        'phone_additional',
        'admin_verify',
        'is_blocked',
        'phone_deleted',
        'email_deleted'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = ['is_have_password'];

    public function getIsHavePasswordAttribute(): bool
    {
        return !empty($this->password);
    }
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'birth_date' => 'datetime',
        'gender' => GenderEnum::class,
    ];

    public function getFioAttribute():string
    {
        $fio = array_filter([$this->last_name, $this->name, $this->second_name]);
        return implode(' ', $fio);
    }

    public function getDisplayNameWithEmailAttribute(): string
    {
        $parts = array_filter([$this->last_name, $this->name]);
        $name = !empty($parts) ? implode(' ', $parts) : ($this->name ?? 'Без имени');
        $email = $this->email ?? 'Без почты';
        return "{$name} ({$email})";
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function routeNotificationForMail($notification)
    {
        return $this->new_email ?? $this->email;
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function passwordResets(): HasMany
    {
        return $this->hasMany(PasswordReset::class);
    }

    public function bonusHistories()
    {
        return $this->hasMany(UserBonusHistory::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class)->where('user_deleted', false);
    }
    public function allAddresses()
    {
        return $this->hasMany(Address::class); // без фильтра
    }


    public function loyaltyLevel()
    {
        return $this->hasOne(UserLoyaltyLevel::class);
    }

    public function whiteList()
    {
        return $this->hasOne(UserWhitelist::class);
    }
    public function avatar()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    public function card()
    {
        return $this->hasOne(BonusCard::class);
    }
    public function setting()
    {
        return $this->hasOne(UserSetting::class);
    }
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function promoNotifications()
    {
        return $this->hasMany(PromoNotification::class);
    }

    public function contactMethods()
    {
        return $this->belongsToMany(ContactMethod::class, 'contact_method_user');
    }


    public function scopeBirthdayToday($query)
    {
        return $query->whereRaw("TO_CHAR(birth_date, 'MM-DD') = ?", [now()->format('m-d')]);
    }

    /**
     * Пользователи, у которых день рождения через 2 дня (начисление бонусов заранее).
     */
    public function scopeBirthdayIn2Days($query)
    {
        return $query->whereRaw("TO_CHAR(birth_date, 'MM-DD') = ?", [now()->addDays(2)->format('m-d')]);
    }
}
