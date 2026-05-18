<?php

namespace User\Models;

use Gradebook\Models\Gradebook;
use Gradebook\Models\GradebookRow;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use User\Enums\UserRole;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->password === null || $user->password === '') {
                unset($user->attributes['password']);
            }
        });
    }

    protected $fillable = [
        'role',
        'first_name',
        'second_name',
        'last_name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'password' => 'hashed',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function gradebooksAsTeacher(): HasMany
    {
        return $this->hasMany(Gradebook::class, 'teacher_id');
    }

    public function gradebookRowsAsStudent(): HasMany
    {
        return $this->hasMany(GradebookRow::class, 'student_id');
    }

    public function defaultApiPath(): string
    {
        return match ($this->role) {
            UserRole::Student => '/api/student/results',
            UserRole::Teacher => '/api/gradebooks',
            UserRole::Administrator => '/api/gradebooks',
        };
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isAdministrator(): bool
    {
        return $this->role === UserRole::Administrator;
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->second_name,
        ])));
    }

    public function getEmailForVerification(): string
    {
        return $this->new_email ?? $this->email;
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->new_email ?? $this->email;
    }
}
