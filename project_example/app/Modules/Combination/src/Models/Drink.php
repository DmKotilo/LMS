<?php

namespace Combination\Models;
use App\Traits\Localization\HasTranslate;
use Combination\Models\Combination;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Product\Models\Product;


class Drink extends Model
{
    use HasFactory,HasTranslate,MultiFileable;


    protected $fillable = [
        'name',
        'description',

    ];
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
            'description' => ['title'=>'Описание', 'type'=>'textarea'],
        ];
    }
    public function combinations()
    {
        return $this->belongsToMany(Combination::class, 'combination_drinks');
    }

    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }


}
