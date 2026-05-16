<?php

namespace File\Interfaces\Factories;

use File\FileObjects\FileObject;
use Illuminate\Http\UploadedFile;

interface FileObjectFactoryInterface
{
    public static function build(array|UploadedFile $file): FileObject;
}
