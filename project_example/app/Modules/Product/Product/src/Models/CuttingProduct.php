<?php

namespace Product\Models;
use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Traits\Localization\HasTranslate;
use Attribute\Models\Attribute;
use Attribute\Models\ProductAttribute;
use Brand\Models\Brand;
use Catalog\Http\Resources\ProductPreviewResource;
use Category\Models\Category;
use Category\Models\ProductCategory;
use Combo\Models\Combo;
use File\Http\Resources\FileResource;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use List\Models\WhitelistItem;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;
use Recipe\Models\RecipeVariant;
use Recipe\Models\SelectionRecipe;
use Review\Models\ProductReview;
use Tag\Models\ProductTag;
use Tag\Models\Tag;


class CuttingProduct extends Model
{
    use HasFactory, MultiFileable;

    protected $fillable = [
        'product_id',
        'content',
        'button_link',
        'button_text',
        'video_link'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

}
