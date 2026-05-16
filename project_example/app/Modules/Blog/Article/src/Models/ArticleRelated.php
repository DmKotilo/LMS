<?php

namespace App\Modules\Blog\Article\src\Models;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\Traits\Localization\HasTranslate;
use Article\Models\Article;
use Article\Models\ArticleSelection;
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



class ArticleRelated extends Model
{
    protected $table = 'article_related';

    protected $fillable = [
        'article_id',
        'related_article_id',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function related()
    {
        return $this->belongsTo(Article::class, 'related_article_id');
    }




}
