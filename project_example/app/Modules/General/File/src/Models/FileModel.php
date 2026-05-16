<?php

namespace File\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileModel extends Model
{
    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'file_name',
        'path',
        'extension',
        'size',
        'disk',
        'type_relation',
        'position'
    ];

    protected $casts = [
        'fileable_id' => 'integer',
        'size' => 'integer',
    ];


    public static function booted(): void {
        self::deleted(function ($model) {
            if ($model->path !== null) {
                Storage::disk('public')->delete($model->path);
            }
        });
    }
}
