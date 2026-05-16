<?php

namespace Brand\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'brand_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

}
