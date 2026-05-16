<?php

namespace Banner\Models;
use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Localization\Models\Lang;
use Localization\Models\Localization;


class Banner extends Model
{
    use HasFactory,MultiFileable, HasTranslate;

    protected $fillable = [
        'title',
        'description',
        'addition_description',
        'link_url',
        'button_text',
        'is_active',
        'banner_type',
        'order',
        'global_promotion_id',
        'location',
        'only_for_new_client',
    ];
    protected $casts = [
        'banner_type' => BannerTypeEnum::class,
        'location' => BannerLocationEnum::class,
        'only_for_new_client' => 'boolean',
    ];
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function globalPromotion(): BelongsTo
    {
        return $this->belongsTo(GlobalPromotion::class, 'global_promotion_id');
    }

    public static function getTransaledField(): array
    {
        return [
            'title' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'text'],
            'button_text' => ['title'=>'Текст кнопки', 'type'=>'text'],
        ];
    }




}
