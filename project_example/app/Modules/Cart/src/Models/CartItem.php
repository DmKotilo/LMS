<?php

namespace App\Modules\Cart\src\Models;

use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use Cart\Models\UserCart;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'item_type', 'item_id','quantity', 'is_gift'];

    protected $casts = [
      'item_type' => CartItemTypeEnum::class,
      'is_gift' => 'boolean',
    ];
    public function item()
    {
        return $this->morphTo();
    }

    public function cart()
    {
        return $this->belongsTo(UserCart::class, 'cart_id');
    }

    public function getItemDisplayNameAttribute()
    {
        return $this->item?->name ?? '[не найден]';
    }

    public function getItemTypeDisplayNameAttribute()
    {
        return $this->item ? class_basename($this->item) : '[не определён]';
    }
}
