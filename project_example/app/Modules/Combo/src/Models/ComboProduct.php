<?php

namespace Combo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboProduct extends Model
{
    use HasFactory;
    
    protected $table = 'combo_product';
    
    public $timestamps = true;
    
    protected $fillable = [
        'combo_id',
        'product_id',
        'price',
    ];
    
    protected $casts = [
        'price' => 'float',
    ];
    
    /**
     * Получить комбонабор
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
    
    /**
     * Получить продукт
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\Product\Models\Product::class, 'product_id');
    }
}

