<?php

namespace Delivery\Models;

use Address\Model\Address;
use App\Modules\Cart\src\Models\CartItem;
use City\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use List\Models\WhitelistItem;
use User\Models\User;


class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'min_order_sum',
        'delivery_price',
        'description',
        'delivery_time',
        'time_intervals'
    ];

    protected $casts = [
        'time_intervals' => 'array',
        'min_order_sum' => 'float',
        'delivery_price' => 'float'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

}
