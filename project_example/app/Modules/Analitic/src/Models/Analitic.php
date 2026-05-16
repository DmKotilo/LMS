<?php

namespace Analitic\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;
use User\Models\User;


class Analitic extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'recomend_click',
        'recomend_to_cart',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

}
