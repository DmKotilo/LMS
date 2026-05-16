<?php

namespace Combination\Models;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class CombinationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'combination_id',
        'category_id',
    ];

    public function combination(){
        return $this->belongsTo(Combination::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }



}
