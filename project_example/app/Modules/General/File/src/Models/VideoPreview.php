<?php

namespace File\Models;

use File\Models\Files\File;
use Illuminate\Database\Eloquent\Model;

class VideoPreview extends Model
{
    protected $table = 'video_previews';

    public $timestamps = false;

    protected $fillable = ['video_id', 'preview_id'];

    // Видео (запись в files)
    public function video()
    {
        return $this->belongsTo(File::class, 'video_id');
    }

    // Превью (тоже запись в files)
    public function preview()
    {
        return $this->belongsTo(File::class, 'preview_id');
    }
}
