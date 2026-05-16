<?php

namespace Authorization\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAuthSetting extends Model
{
    protected $fillable = ['name', 'is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];
}
