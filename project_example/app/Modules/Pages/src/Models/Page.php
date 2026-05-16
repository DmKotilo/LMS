<?php

namespace Pages\Models;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Localization\Models\Lang;
use Localization\Models\Localization;
use App\Observers\PageObserver;


#[ObservedBy([PageObserver::class])]
class Page extends Model
{
    use HasFactory, MultiFileable, HasTranslate;

    protected $fillable = [
        'seo_title',
        'seo_description',
        'keywords',
        'slug',
        'content',
        'is_active',
    ];

    public static function getTransaledField(): array
    {
        return [
            'seo_title' => ['title'=>'Название', 'type'=>'text'],
            'seo_description' => ['title'=>'Описание', 'type'=>'text'],
//            'content' => ['title'=>'Текст кнопки', 'type'=>'wysiwig'],
        ];
    }




}
