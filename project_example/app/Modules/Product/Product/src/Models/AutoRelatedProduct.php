<?php

namespace Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoRelatedProduct extends Model
{
    use HasFactory;

    protected $table = 'auto_related_products';

    protected $fillable = [
        'product_id',
        'related_product_id',
        'joint_orders_count',
        'product_orders_count',
        'confidence',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function related()
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}

