<?php

namespace Setting\Models;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;


class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'description',
        'value',
        'value_type',
    ];

    protected static function booted()
    {
        static::saving(function ($setting) {
            $setting->value = (string) $setting->value;
        });
    }

    public function getValueAttribute($value)
    {
        return match ($this->value_type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'string' => (string) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
