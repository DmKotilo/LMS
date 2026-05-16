<?php

namespace Pages\Models;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Localization\Models\Lang;
use Localization\Models\Localization;


class Reward extends Model
{
    use HasFactory, MultiFileable;

    protected $fillable = [
        "name",
        "position"
    ];

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
