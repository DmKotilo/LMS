<?php

namespace Category\Models;
use Category\Http\Resources\CategoryResource;
use Combination\Models\Combination;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Product\Models\Product;
use App\Observers\CategoryObserver;


#[ObservedBy([CategoryObserver::class])]
class Category extends Model
{
    use HasFactory,MultiFileable;

    protected $fillable = [
        'name',
        'slug',
        'parent_category_id',
        'explanation',
        'description',
        'order',
        'has_second_item_discount',

        'seo_title',
        'seo_description',
        'keywords',
    ];

    protected $casts = [
        'lvl' => 'integer',
        'has_second_item_discount' => 'boolean',
    ];

    public static function boot() {
        parent::boot();
        self::saving(function (Category $model) {
            $lvl = 1;
            $recursionModel = $model->parent;
            if($recursionModel) $lvl++;
            while($recursionModel->parent ?? false) {
                $lvl++;
                $recursionModel = $recursionModel->parent;
            }
            $model->lvl = $lvl;

            if ($model->parent) {
                $model->has_second_item_discount = (bool) $model->parent->has_second_item_discount;
            }
        });

        self::saved(function (Category $model) {
            if ($model->wasChanged('has_second_item_discount')) {
                $model->syncChildrenDiscount((bool) $model->has_second_item_discount);
            }
        });
    }

//    static function getParentLvl()
//    {
//        return self::whereNull('parent_category_id')
//            ->orderBy('order', 'asc');
//    }
    static function getParentLvl()
    {
        return self::whereNull('parent_category_id')
            ->orderByRaw('"order" IS NULL ASC')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function getFullBreadcrumbAttribute(): string
    {
        $path = [];
        $current = $this;

        while ($current && $current->parent) {
            $path[] = $current->parent->name;
            $current = $current->parent;
        }

        $path = array_reverse($path);
        $path[] = $this->name;

        return implode(' -> ', $path);
    }

    static function getTree()
    {
//        $categoryLvl_1 = self::whereNull('parent_category_id')->orderBy('order','asc')->get();
        $categoryLvl_1 = self::whereNull('parent_category_id')
            ->orderByRaw('"order" IS NULL ASC')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        return CategoryResource::collection($categoryLvl_1);
    }

    //TODO удалить спустя время, эта функция не нужна, есть функция getTree()
/*    static function getTreeOrdered($parentId = null)
    {
        $categories = self::where('parent_category_id', $parentId)->get();

        $tree = [];

        foreach ($categories as $category) {
            $tree[] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->image ? $category->image->path : null,
                'slug' => $category->slug,
                'children' => self::getTreeOrdered($category->id),
            ];
        }

        return $tree;
    }*/

    public function combinations()
    {
        return $this->belongsToMany(Combination::class, 'combination_categories');
    }
    static function getIdCategoriesChild(Category $category) : array
    {
        $idArray = [$category->id];

        return $idArray;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function syncChildrenDiscount(bool $hasDiscount): void
    {
        $this->children()->get()->each(function (Category $child) use ($hasDiscount) {
            if ($child->has_second_item_discount !== $hasDiscount) {
                $child->forceFill(['has_second_item_discount' => $hasDiscount])->saveQuietly();
            }

            $child->syncChildrenDiscount($hasDiscount);
        });
    }
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function getSlugPathAttribute(): string
    {
        $segments = [];
        $current = $this;

        while ($current) {
            array_unshift($segments, $current->slug);
            $current = $current->parent;
        }

        return implode('/', $segments);
    }

}
