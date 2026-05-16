<?php

namespace App\Modules\Faq\src\Models;

use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Faq\src\Enums\FaqSectionPageSlugEnum;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Traits\Localization\HasTranslate;
use Cart\Models\UserCart;
use Faq\Models\Faq;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;

class FaqSection extends Model
{
    use HasFactory, HasTranslate;

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'page_slug' => FaqSectionPageSlugEnum::class,
    ];
    public function faqs()
    {
        return $this->hasMany(Faq::class, 'section_id');
    }
    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
        ];
    }

    public function scopeForPageSlug(Builder $query, ?string $pageSlug): void
    {
        $query->where('page_slug', $pageSlug);
    }

    public function scopeGeneral(Builder $query): void
    {
        $query->whereNull('page_slug');
    }
}
