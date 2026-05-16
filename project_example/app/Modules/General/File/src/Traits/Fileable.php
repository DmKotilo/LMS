<?php

namespace File\Traits;

use File\Factories\FileObjectFactory;
use File\FileObjects\FileObject;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

trait Fileable
{
    abstract public function files(): MorphOneOrMany;

    /**
     * @return \Illuminate\Database\Eloquent\Model|null;
     */
    public function storeFile(FileObject $file, ?string $directory = null, ?string $relation = null)
    {
        try {
            $file->store($directory);

            DB::beginTransaction();
            $fileRecord = $this->files()->create([
                'file_name' => $file->name,
                'path' => $file->path,
                'extension' => $file->extension,
                'size' => $file->size,
                'disk' => $file::getDisk(),
                'type_relation' => $relation,
            ]);

            DB::commit();

            return $fileRecord;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function makeFile(UploadedFile|File|array $data): FileObject
    {
        return FileObjectFactory::build($data);
    }

    public function disk(string $disk): self
    {
        FileObject::setDisk($disk);

        return $this;
    }
}
