<?php

namespace Filter\Models;
use Brand\Models\Brand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use List\Models\WhitelistItem;
use Tag\Models\Tag;
use User\Models\User;


class SavedFilter extends Model
{
    protected $fillable = ['user_id', 'price_from', 'price_to', 'brand_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributes()
    {
        return $this->hasMany(SavedFilterAttribute::class, 'saved_filter_id');
    }
    public function tags()
    {
        return $this->belongsToMany(
            \Tag\Models\Tag::class,
            'saved_filter_tags',
            'saved_filter_id',
            'tag_id'
        );
    }
    public function inWhitelist(): MorphOne
    {
        return $this->morphOne(WhitelistItem::class, 'item');
    }
}

