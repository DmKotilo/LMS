<?php

namespace Category\Models;
use Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MainCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'text_color',
        'desktop_photo',
        'mobile_photo',
        'category_id',
        'is_active',
        'position',
        'desktop_font_size',
        'mobile_font_size',
        'desktop_line_height',
        'mobile_line_height',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }


}
