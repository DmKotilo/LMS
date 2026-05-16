<?php

namespace Log\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Social extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function userSocialLogs()
    {
        return $this->hasMany(UserSocialLog::class);
    }

}
