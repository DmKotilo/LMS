<?php

namespace Filter\Models;
use Attribute\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SavedFilterAttribute extends Model
{
    protected $fillable = ['saved_filter_id', 'attribute_id', 'value'];

    public function filter()
    {
        return $this->belongsTo(SavedFilter::class, 'saved_filter_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}

