<?php

namespace Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'confirmation_subject',
        'code',
        'status',
        'target',
    ];
}
