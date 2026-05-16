<?php

namespace UserSetting\Models;
use App\Modules\UserSetting\src\Enums\OftenTypeEnum;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_notifications',
        'user_id',
        'sale_notifications',
        'email_notifications',
        'sms_notifications',
        'messenger_notifications',
        'favorite_categories',
        'often_type',
    ];

    protected $casts = [
        'often_type' => OftenTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoriteCategories()
    {
        return $this->belongsToMany(
            Category::class,
            'user_setting_favorite_categories',
            'user_setting_id',
            'category_id'
        );
    }

}
