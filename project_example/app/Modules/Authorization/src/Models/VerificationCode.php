<?php

namespace Authorization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use User\Models\User;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    public $timestamps = true;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
