<?php

namespace Article\Models;
use App\Modules\Blog\Article\src\Models\ArticleRelated;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Traits\Localization\HasTranslate;
use Category\Models\Category;
use Comment\Models\Comment;
use File\Models\Files\File;
use File\Traits\Fileable;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Product\Models\Product;
use Review\Models\Review;
use App\Observers\ArticleObserver;

#[ObservedBy([ArticleObserver::class])]
class Article extends Model
{
    use HasFactory, MultiFileable, HasTranslate;
    protected $fillable = [
        'title',
        'description',
        'mini_description',
        'author',
        'tags',
        'is_popular',
        'text',
        'publish',

        'seo_title',
        'seo_description',
        'keywords',
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
            'description' => ['title'=>'Описание', 'type'=>'longText'],
            'mini_description' => ['title'=>'Краткое описание', 'type'=>'text'],
            'text' => ['title'=>'Контент', 'type'=>'longText'],
        ];
    }

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    public function selections(): BelongsToMany
    {
        return $this->belongsToMany(
            ArticleSelection::class,
            'article_selection_article',
            'article_id',
            'article_selection_id'
        )->withPivot(['order_number','is_displayed'])->withTimestamps();
    }
    public function relatedArticles()
    {
        return $this->hasMany(ArticleRelated::class, 'article_id');
    }

    public function relatedTo()
    {
        return $this->hasMany(ArticleRelated::class, 'related_article_id');
    }


    public function comments() // Новое отношение для всех комментариев
    {
        // Предполагается, что в модели Comment определено отношение morphTo('item')
        return $this->morphMany(Comment::class, 'item');
    }

    public function approvedComments() // Новое отношение для утвержденных комментариев
    {
        // 'comments()' - вызывает метод отношения выше
        // 'approved()' - вызывает scope из модели Comment
        return $this->comments()->approved();
    }

//    public function reviews()
//    {
//        return $this->morphMany(Review::class, 'item');
//    }


}
