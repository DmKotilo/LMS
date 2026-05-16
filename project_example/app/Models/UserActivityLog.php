<?php

namespace App\Models;

use App\Enums\UserActivityStatusEnum;
use App\Enums\UserActivityTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use User\Models\User;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'status',
        'ip_address',
        'user_agent',
        'metadata',
        'social_provider',
        'notification_type',
        'notification_status',
        'related_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'activity_type' => UserActivityTypeEnum::class,
        'status' => UserActivityStatusEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Типы действий
     */
    const TYPE_REGISTRATION = 'registration';
    const TYPE_LOGIN = 'login';
    const TYPE_SOCIAL_AUTH = 'social_auth';
    const TYPE_PASSWORD_RESET_REQUEST = 'password_reset_request';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_NOTIFICATION_SENT = 'notification_sent';

    /**
     * Статусы действий
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * Провайдеры социальных сетей
     */
    const PROVIDER_VKONTAKTE = 'vkontakte';
    const PROVIDER_YANDEX = 'yandex';

    /**
     * Типы уведомлений
     */
    const NOTIFICATION_NOVELTY = 'novelty';
    const NOTIFICATION_PROMO = 'promo';

    /**
     * Статусы доставки уведомлений
     */
    const DELIVERY_STATUS_PENDING = 'pending';
    const DELIVERY_STATUS_SENT = 'sent';
    const DELIVERY_STATUS_FAILED = 'failed';

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

