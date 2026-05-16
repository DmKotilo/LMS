<?php

namespace Localization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LangStaticKeyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_default'
    ];

    protected $casts = [

    ];

    public function lang_static_keys(): HasMany
    {
        return $this->hasMany(LangStaticKey::class);
    }

}
