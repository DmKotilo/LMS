<?php

namespace SeoPage\Models;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;


class SeoPage extends Model
{
    use HasFactory;
//HasTranslate добавить если будут переводы
    protected $fillable = [
        'page_name',
        'slug',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

//    public static function getTransaledField(): array
//    {
//        return [
//            'title' => ['title'=>'Название', 'type'=>'text'],
//            'description' => ['title'=>'Описание', 'type'=>'text'],
//            'button_text' => ['title'=>'Текст кнопки', 'type'=>'text'],
//        ];
//    }




}
