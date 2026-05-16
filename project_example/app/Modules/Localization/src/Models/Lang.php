<?php

namespace Localization\Models;

use App\Observers\LangObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[ObservedBy([LangObserver::class])]
class Lang extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'static_translation'
    ];

    protected $casts = [
        'title' => 'string',
        'static_translation' => 'json',
    ];

    public function localizations(): HasMany
    {
        return $this->hasMany(Localization::class, 'lang_id');
    }
}
