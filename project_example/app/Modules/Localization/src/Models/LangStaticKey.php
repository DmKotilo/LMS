<?php

namespace Localization\Models;

use App\Observers\LangObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class LangStaticKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'lang_static_key_group_id',
        'description',
        'default',
        'default_en',
        'is_default'
    ];

    protected $casts = [

    ];

    public function lang_static_key_group(): BelongsTo
    {
        return $this->belongsTo(LangStaticKeyGroup::class);
    }

    public function langStaticKeyGroup(): BelongsTo //для МУНШАЙНА relatedLink
    {
        return $this->belongsTo(LangStaticKeyGroup::class);
    }
}
