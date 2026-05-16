<?php

namespace Recipe\Models;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use List\Models\WhitelistItem;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;
use Recipe\Models\RecipeVariant;
use Review\Models\Review;



class RecipeSelection extends Model
{
    use HasFactory,MultiFileable, HasTranslate;

    protected $fillable = [
        'title',
        'order_number',
        'is_displayed',
    ];

    public static function getTransaledField(): array
    {
        return [
            'title' => ['title'=>'Название', 'type'=>'text'],
        ];
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_selection_recipe',
            'recipe_selection_id',
            'recipe_id'
        )->withPivot(['order_number','is_displayed'])->withTimestamps();
    }

}
