<?php

namespace Combination\Models;
use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use App\Traits\Localization\HasTranslate;
use Category\Models\Category;
use City\Models\City;
use Combo\Models\Combo;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Product\Models\Product;
use App\Observers\CombinationObserver;


#[ObservedBy([CombinationObserver::class])]
class Combination extends Model
{
    use HasFactory,HasTranslate,MultiFileable;


    protected $fillable = [
        'name',
        'description',
        'cooking_method',
        'product_id',
        'sauce_title',
        'garnish_title',
        'drink_title',
        'is_active',

        'seo_title',
        'seo_description',
        'keywords',
    ];
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'textarea'],
            'cooking_method' => ['title'=>'Способы приготовления', 'type'=>'text'],
            'sauce_title' => ['title'=>'Заголовок для соусов', 'type'=>'text'],
            'drink_title' => ['title'=>'Заголовок для напитков', 'type'=>'text'],
            'garnish_title' => ['title'=>'Заголовок для гарниров', 'type'=>'text'],
            ];
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'combination_categories');
    }
    public function combination_categories()
    {
        return $this->hasMany(CombinationCategory::class);
    }

    public function garnishes()
    {
        return $this->belongsToMany(Garnish::class, 'combination_garnishes');
    }
    public function sauces()
    {
        return $this->belongsToMany(Sauce::class, 'combination_sauces');
    }

    // напитки
    public function drinks()
    {
        return $this->belongsToMany(Drink::class, 'combination_drinks');
    }

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }


}
