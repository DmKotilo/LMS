<?php

namespace File\Traits;

use File\FileObjects\FileObject;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

trait HasFile
{
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function bootHasFile()
    {
        static::deleting(function ($model) {
            Storage::disk($model->disk)->delete($model->path);
        });
    }

    public function updateFile(FileObject $file, ?string $directory = null): self
    {
        Storage::disk($this->disk)->delete($this->path);

        $file->store($directory ?? dirname($this->path));

        $this->update([
            'file_name' => $file->name,
            'path' => $file->path,
            'extension' => $file->extension,
            'size' => $file->size,
            'disk' => $file::getDisk(),
        ]);

        return $this;
    }
}
