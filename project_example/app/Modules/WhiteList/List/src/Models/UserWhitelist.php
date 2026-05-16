<?php

namespace List\Models;
use Filter\Models\SavedFilterAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;
use Recipe\Models\Recipe;
use User\Models\User;


class UserWhitelist extends Model
{
    protected $fillable = ['user_id'];

    public function items()
    {
        return $this->hasMany(WhitelistItem::class, 'whitelist_id');
    }

    public function products()
    {
        return $this->items()->where('item_type', Product::class);
    }

    public function recipes()
    {
        return $this->items()->where('item_type', Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
