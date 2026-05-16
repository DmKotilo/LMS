<?php

namespace Article\Models;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Traits\Localization\HasTranslate;
use Category\Models\Category;
use File\Models\Files\File;
use File\Traits\Fileable;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Product\Models\Product;



class ArticleSelection extends Model
{
    use HasFactory, MultiFileable, HasTranslate;

    protected $fillable = [
        'title',
        'description',
        'order_number',
        'views_count',
        'is_popular',
        'is_displayed',
        'is_main',
    ];

    protected $casts = [
        'blog_category_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'text' => 'string',
        'publish' => 'boolean',
        'show_main' => 'boolean',
    ];
    public static function getTransaledField(): array
    {
        return [
            'title' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'text'],
        ];
    }
    public function image(){
        return $this->morphOne(File::class,'fileable');
    }
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'article_selection_article',
            'article_selection_id',
            'article_id'
        )->withPivot(['order_number','is_displayed'])->withTimestamps();
    }



}
