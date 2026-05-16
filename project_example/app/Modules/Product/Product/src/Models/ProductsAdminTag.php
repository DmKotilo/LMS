<?php

namespace Product\Models;

use App\Modules\Product\Product\src\Models\AdminTag;
use Illuminate\Database\Eloquent\Model;



class ProductsAdminTag extends Model
{
    protected $table = 'products_admin_tags';

    protected $fillable = [
        'product_id',
        'related_product_id',
    ];

    // связь на сам продукт
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // связь на связанный продукт
    public function tag()
    {
        return $this->belongsTo(AdminTag::class);
    }
}
