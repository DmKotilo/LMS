<?php

namespace Promotion\Models;
use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use App\Traits\Localization\HasTranslate;
use City\Models\City;
use Combo\Models\Combo;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Product\Models\Product;
use App\Observers\PromotionObserver;


#[ObservedBy([PromotionObserver::class])]
class Promotion extends Model
{
    use HasFactory,HasTranslate,MultiFileable;


    protected $fillable = [
        'name',
        'short_description',
        'type',
        'end_date',
        'is_active',
        'sale_percent',

        'seo_title',
        'seo_description',
        'keywords',
    ];
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'short_description' => ['title'=>'Короткое описание', 'type'=>'textarea'],
        ];
    }

    protected $casts = [
        'type' => PromotionTypeEnum::class,
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];
    public function cities()
    {
        return $this->belongsToMany(City::class, 'promotion_city');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'promotion_combo');
    }
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }


}
