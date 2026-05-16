<?php

namespace Order\Models;
use App\Traits\Localization\HasTranslate;
use File\Models\Files\File;
use File\Traits\MultiFileable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Order\Models\Order;
use Product\Models\Product;


class TransactionMethod extends Model
{
    use HasFactory, HasTranslate;


    protected $fillable = [
        'name',
        'is_active',
        'description'
    ];

    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
        ];
    }
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }

}
