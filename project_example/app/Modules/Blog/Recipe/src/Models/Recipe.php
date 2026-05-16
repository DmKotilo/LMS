<?php

namespace Recipe\Models;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use List\Models\WhitelistItem;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Product\Models\Product;
use Review\Models\Review;
use App\Observers\RecipeObserver;



#[ObservedBy([RecipeObserver::class])]
class Recipe extends Model
{
    use HasFactory,MultiFileable, HasTranslate;

    protected $fillable = [
        'title',
        'mini_description',
        'publish',
        'show_main',
        'cooking_time_minutes',
        'difficult',
        'number_of_persons',
        'description',
        'content',
        'ingredients_for_dish_json',
        'ingredients_for_sauce_json',
        'recipe_steps_json',
        'active_cooking_time_minutes',

        'seo_title',
        'seo_description',
        'keywords',
    ];

    protected $casts = [
        'title' => 'string',
        'mini_description' => 'string',
        'text' => 'string',
        'publish' => 'boolean',
        'show_main' => 'boolean',
        'cooking_time_minutes' => 'integer',
        'difficult' => 'integer',
        'number_of_persons' => 'integer',
        'ingredients_for_dish_json' => 'array',
        'ingredients_for_sauce_json' => 'array',
        'recipe_steps_json' => 'array',
    ];


    public function getStepsAttribute(): array
    {
        return $this->recipe_steps_json ?? [];
    }

    public function setStepsAttribute(array $value): void
    {
        $this->attributes['recipe_steps_json'] = json_encode($value);
    }

    public function getRatingAttribute(): int
    {
        $approvedReviews = $this->reviews()
            ->where('status', ReviewStatusEnum::Approved)
            ->get();

        $approvedCount = $approvedReviews->count();

        if ($approvedCount === 0) {
            return 0;
        }

        $averageRating = $approvedReviews->avg('rating');

        return (int) round($averageRating);
    }

    public static function getTransaledField(): array
    {
        return [
            'title' => ['title'=>'Название', 'type'=>'text'],
            'mini_description' => ['title'=>'Короткое описание', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'long_text'],
        ];
    }

    public function selections(): BelongsToMany
    {
        return $this->belongsToMany(
            RecipeSelection::class,
            'recipe_selection_recipe',
            'recipe_id',
            'recipe_selection_id'
        )->withPivot(['order_number','is_displayed'])->withTimestamps();
    }
    public function variants(): MorphToMany
    {
        return $this->morphToMany(
            RecipeVariant::class,
            'recipe_variantable',
            'recipe_variantables',
            'recipe_variantable_id',
            'recipe_variant_id'
        );
    }
    public function images()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'images')
            ->orderByRaw('COALESCE(position, 0), id');
    }

    public function videos()
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('type_relation', 'videos')
            ->orderByRaw('COALESCE(position, 0), id');
    }

    public function firstImage()
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('type_relation', 'images')
            ->orderByRaw('COALESCE(position, 0), id');
    }
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'recipe_to_products',
            'recipe_id',
            'product_id'
        );
    }
    public function inWhitelist(): MorphOne
    {
        return $this->morphOne(WhitelistItem::class, 'item');
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'item');
    }


}
