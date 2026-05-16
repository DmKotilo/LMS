<?php

namespace Brand\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_1c',
        'slug',
        'is_active',
        'uuid_1c',
        'explanation',
        'description',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_brands');
    }

    public function children()
    {
        return $this->hasMany(Brand::class, 'parent_brand_id');
    }

}
