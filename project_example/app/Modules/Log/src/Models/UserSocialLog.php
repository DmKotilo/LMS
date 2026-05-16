<?php

namespace Log\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log\Enums\AuthTypeEnum;
use Log\Enums\LogStatusEnum;
use User\Models\User;


class UserSocialLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'social_id',
        'auth_type',
        'status',
        'ip',
    ];

    protected $casts = [
      'auth_type' =>  AuthTypeEnum::class,
      'status' =>  LogStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function social()
    {
        return $this->belongsTo(Social::class);
    }



}
