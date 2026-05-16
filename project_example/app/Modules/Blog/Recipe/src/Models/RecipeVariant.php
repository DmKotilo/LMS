<?php

namespace Recipe\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Product\Models\Product;


class RecipeVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function recipes(): MorphToMany
    {
        return $this->morphedByMany(
            Recipe::class,
            'recipe_variantable',
            'recipe_variantables',
            'recipe_variant_id',
            'recipe_variantable_id'
        );
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            Product::class,
            'recipe_variantable',
            'recipe_variantables',
            'recipe_variant_id',
            'recipe_variantable_id'
        );
    }
}
