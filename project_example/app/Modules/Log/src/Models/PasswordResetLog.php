<?php

namespace Log\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log\Enums\LogStatusEnum;
use User\Models\User;


class PasswordResetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'ip',
    ];

    protected $casts = [
      'status' => LogStatusEnum::class,
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }



}
