<?php

namespace App\Modules\Product\Product\src\Models;
use App\Interfaces\Interfaces\CartItemPriceable;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\RecomendProductPageEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use List\Models\WhitelistItem;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Manufacturer\Models\Manufacturer;
use Product\Models\CuttingProduct;
use Product\Models\Product;
use Product\Models\ProductsAdminTag;
use Recipe\Models\RecipeVariant;
use Recipe\Models\SelectionRecipe;
use Review\Models\ProductReview;
use Review\Models\Review;
use Tag\Models\ProductTag;
use Tag\Models\Tag;


class AdminTag extends Model
{
    use HasFactory, MultiFileable;

    protected $fillable = [
        'title',
    ];

    public function products()
    {
        return $this->hasMany(ProductsAdminTag::class);
    }


}
