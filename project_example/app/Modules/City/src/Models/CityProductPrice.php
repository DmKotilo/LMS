<?php

namespace App\Modules\City\src\Models;
use Address\Model\Address;
use App\Observers\CityProductPriceObserver;
use App\Traits\Localization\HasTranslate;
use City\Models\City;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;

#[ObservedBy([CityProductPriceObserver::class])]
class CityProductPrice extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'product_id', 'price', 'price_per_kg'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
