<?php

namespace Attribute\Models;
use Attribute\Enums\InputTypeEnum;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'input_type',
        'slug',
        'is_default',
        'is_required',
        'is_active',
        'options',
        'is_many_checked_options',
        'is_select_writable',
        'is_show_filter',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'attribute_categories');
    }

    public function attribute_categories()
    {
        return $this->hasMany(AttributeCategory::class);
    }

    protected $casts = [
        'input_type' => InputTypeEnum::class,
        'is_default' => 'bool',
        'is_required' => 'bool',
        'is_active' => 'bool',
        'options' => 'array',
        'is_many_checked_options' => 'bool',
        'is_select_writable' => 'bool',
        'is_show_filter' => 'bool',
    ];

}
