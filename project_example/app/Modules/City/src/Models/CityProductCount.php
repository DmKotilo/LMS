<?php

namespace App\Modules\City\src\Models;
use Address\Model\Address;
use App\Traits\Localization\HasTranslate;
use City\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class CityProductCount extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'product_id', 'quantity'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
