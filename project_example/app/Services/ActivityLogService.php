<?php

namespace App\Services;

use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use User\Models\User;

class ActivityLogService
{
    /**
     * Логирование регистрации
     */
    public function logRegistration(User $user, Request $request, bool $success = true): void
    {
        UserActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => UserActivityLog::TYPE_REGISTRATION,
            'status' => $success ? UserActivityLog::STATUS_SUCCESS : UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Логирование неуспешной попытки регистрации
     */
    public function logRegistrationFailed(Request $request, array $errors = []): void
    {
        UserActivityLog::create([
            'user_id' => null,
            'activity_type' => UserActivityLog::TYPE_REGISTRATION,
            'status' => UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'email' => $request->input('email'),
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Логирование входа
     */
    public function logLogin(?User $user, Request $request, bool $success = true, ?string $email = null): void
    {
        UserActivityLog::create([
            'user_id' => $user?->id,
            'activity_type' => UserActivityLog::TYPE_LOGIN,
            'status' => $success ? UserActivityLog::STATUS_SUCCESS : UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'email' => $email ?? $user?->email,
            ],
        ]);
    }

    /**
     * Логирование входа через социальные сети
     */
    public function logSocialAuth(?User $user, Request $request, string $provider, bool $success = true, bool $isNewUser = false): void
    {
        UserActivityLog::create([
            'user_id' => $user?->id,
            'activity_type' => UserActivityLog::TYPE_SOCIAL_AUTH,
            'status' => $success ? UserActivityLog::STATUS_SUCCESS : UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'social_provider' => $provider,
            'metadata' => [
                'is_new_user' => $isNewUser,
                'email' => $user?->email,
            ],
        ]);
    }

    /**
     * Логирование запроса на восстановление пароля
     */
    public function logPasswordResetRequest(?User $user, Request $request, bool $success = true, ?string $email = null): void
    {
        UserActivityLog::create([
            'user_id' => $user?->id,
            'activity_type' => UserActivityLog::TYPE_PASSWORD_RESET_REQUEST,
            'status' => $success ? UserActivityLog::STATUS_SUCCESS : UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'email' => $email ?? $user?->email,
                'phone' => $request->input('phone'),
            ],
        ]);
    }

    /**
     * Логирование изменения пароля
     */
    public function logPasswordReset(User $user, Request $request, bool $success = true): void
    {
        UserActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => UserActivityLog::TYPE_PASSWORD_RESET,
            'status' => $success ? UserActivityLog::STATUS_SUCCESS : UserActivityLog::STATUS_FAILED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Логирование отправки уведомления
     */
    public function logNotificationSent(
        User $user,
        string $notificationType,
        string $status = UserActivityLog::DELIVERY_STATUS_SENT,
        ?int $relatedId = null,
        ?array $metadata = null
    ): void {
        UserActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => UserActivityLog::TYPE_NOTIFICATION_SENT,
            'status' => UserActivityLog::STATUS_SUCCESS,
            'notification_type' => $notificationType,
            'notification_status' => $status,
            'related_id' => $relatedId,
            'metadata' => $metadata ?? [],
        ]);
    }
}

