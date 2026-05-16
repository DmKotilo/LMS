<?php

namespace Tag\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class Tag extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tags');
    }

}
