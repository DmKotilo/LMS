<?php

namespace ContactForm\Models;
use App\Modules\ContactForm\src\Http\Enums\ContactFormStatusEnum;
use App\Modules\ContactForm\src\Http\Enums\ContactFormTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;
use User\Models\User;


class CallContactForm extends Model
{
    use HasFactory;
    protected $table = 'call_contact_forms';
    protected $fillable = [
        'name',
        'phone',
        'comment',
        'time_interval',
        'status',
        'post',//должность
        'email',
        'type',
    ];
    protected $casts = [
        'type' => ContactFormTypeEnum::class,
        'status' => ContactFormStatusEnum::class,
    ];

}
