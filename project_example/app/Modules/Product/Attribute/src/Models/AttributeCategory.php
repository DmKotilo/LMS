<?php

namespace Attribute\Models;

use Attribute\Models\Attribute;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class AttributeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'attribute_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

}
