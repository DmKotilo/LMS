<?php

namespace Faq\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Faq\src\Models\FaqSection;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Traits\Localization\HasTranslate;
use Cart\Models\UserCart;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;

class Faq extends Model
{
    use HasFactory, MultiFileable, HasTranslate;

    protected $fillable = [
        'section_id',
        'position',
        'name',
        'description',
        'button_text',
        'link_button',
    ];

    public function section()
    {
        return $this->belongsTo(FaqSection::class, 'section_id');
    }


    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'long_text'],
            'button_text' => ['title'=>'Текст кнопки', 'type'=>'text'],
        ];
    }



}
