<?php

namespace File\Models\Files;

use File\Models\FileModel;
use File\Models\VideoPreview;
use File\Traits\HasFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends FileModel
{
    use HasFactory, HasFile;

    protected $table = 'files';



    public function preview()
    {
        return $this->hasOne(VideoPreview::class, 'video_id');
    }

    public function previewOf()
    {
        return $this->belongsTo(VideoPreview::class, 'id', 'preview_id');
    }
}
