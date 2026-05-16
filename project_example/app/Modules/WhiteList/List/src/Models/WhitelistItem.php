<?php

namespace List\Models;
use Filter\Models\SavedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class WhitelistItem extends Model
{
    protected $fillable = ['whitelist_id', 'item_type', 'item_id'];

    public function item()
    {
        return $this->morphTo();
    }

    public function whitelist()
    {
        return $this->belongsTo(UserWhitelist::class, 'whitelist_id');
    }
}
