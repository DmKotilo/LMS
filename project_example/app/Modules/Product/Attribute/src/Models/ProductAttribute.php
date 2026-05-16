<?php

namespace Attribute\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'explanation',
        'product_id',
        'attribute_id',
    ];

    protected $casts = [
/*        'value' => 'json',*/ //с JSON не работает фильтр в каталоге по типу упаковки
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

}
