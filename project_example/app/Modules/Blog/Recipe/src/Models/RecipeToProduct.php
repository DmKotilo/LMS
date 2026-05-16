<?php

namespace Recipe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Product\Models\Product;

class RecipeToProduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'product_id',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
