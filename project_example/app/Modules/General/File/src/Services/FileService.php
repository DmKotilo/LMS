<?php

namespace App\Modules\General\File\src\Services;

use App\Modules\General\File\src\Interfaces\FileServiceInterface;
use File\FileObjects\FileObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService implements FileServiceInterface
{


    public function uploadFile(UploadedFile $uploadedFile, Model $model, string $relation, bool $clearOldFiles = false)
    {
        if ($clearOldFiles) $this->clearOldFiles($model, $relation);

        $fileObject = new FileObject(
            name: $uploadedFile->getClientOriginalName(),
            content: file_get_contents($uploadedFile->getRealPath()),
            path: '',
            extension: $uploadedFile->getClientOriginalExtension(),
            size: $uploadedFile->getSize()
        );

        $className = strtolower(class_basename($model));
        $model->selectFileRelation($relation)->storeFile(
            $fileObject,
            "$className/{$model->id}/$relation"
        );
    }

    public function attachExistingImage(string $filePath, Model $model, string $relation, bool $clearOldFiles = false)
    {
        if ($clearOldFiles) {
            $this->clearOldFiles($model, $relation);
        }

        if (str_starts_with($filePath, 'http')) {
            $filePath = str_replace(asset(''), '', $filePath);
        }

        $absolutePath = public_path($filePath);

        if (!file_exists($absolutePath)) {
            throw new \Exception("File not found at path: {$absolutePath}");
        }

        $fileInfo = pathinfo($absolutePath);

        $fileObject = new FileObject(
            name: $fileInfo['basename'],
            content: file_get_contents($absolutePath),
            path: '',
            extension: $fileInfo['extension'] ?? '',
            size: filesize($absolutePath)
        );

        $className = strtolower(class_basename($model));
        $model->selectFileRelation($relation)->storeFile(
            $fileObject,
            "$className/{$model->id}/$relation"
        );
    }

    public function clearOldFiles(Model $model, string $relation)
    {

        $model->$relation()->delete();
    }
}
