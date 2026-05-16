<?php

namespace App\Modules\General\File\src\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface FileServiceInterface
{
    public function uploadFile(UploadedFile $uploadedFile, Model $model, string $relation, bool $clearOldFiles = false);
    public function attachExistingImage(string $filePath, Model $model, string $relation, bool $clearOldFiles = false);
    public function clearOldFiles(Model $model, string $relation);

}
