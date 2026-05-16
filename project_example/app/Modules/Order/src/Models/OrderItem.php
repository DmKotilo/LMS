<?php

namespace Order\Models;

use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use Cart\Models\UserCart;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'price',//цена из корзины со всеми скидками
        'price_for_unit',//
        'price_for_unit_without_sale',
        'unit_sale_percent',
        'quantity',
        'weight',
        'weight_for_unit',
        'weight_type',
        'is_gift',
    ];
    protected $casts = [
        'item_type' => CartItemTypeEnum::class,
        'is_gift' => 'boolean',
    ];
    public function item()
    {
        return $this->morphTo();
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
