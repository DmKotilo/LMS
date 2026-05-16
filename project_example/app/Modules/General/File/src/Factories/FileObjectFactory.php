<?php

namespace File\Factories;

use File\Exceptions\FileModuleException;
use File\FileObjects\FileObject;
use File\Interfaces\Factories\FileObjectFactoryInterface;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class FileObjectFactory implements FileObjectFactoryInterface
{
    /**
     * Creates a Module FileObject from a base64 string or Laravel File Objects
     */
    public static function build(array|UploadedFile|File $file): FileObject
    {
        try {
            return match (true) {
                is_array($file) => self::buildFileFromBase64($file),
                $file instanceof File => self::buildFileFromFileHelperTrait($file),
                $file instanceof UploadedFile => self::buildFileFromFileHelperTrait($file),
                default => throw new \Exception('The variable in the match construct is of the wrong type'),
            };
        } catch (\Exception $th) {
            throw new FileModuleException($th->getMessage(), 400);
        }
    }

    private static function buildFileFromBase64(array $file): FileObject
    {
        self::isValidArray($file);

        $decoded_data = base64_decode($file['base64']);

        fwrite($tmpFile = tmpfile(), $decoded_data);

        $path = stream_get_meta_data($tmpFile)['uri'];
        $tmpFileObj = new File($path);
        $content = file_get_contents($path);
        $size = filesize($path) ?? null;

        return new FileObject(
            name: $file['name'] ?? uniqid(),
            content: $content,
            path: $path,
            extension: $tmpFileObj->extension(),
            size: $size,
        );
    }

    private static function buildFileFromFileHelperTrait(mixed $file): FileObject
    {
        return new FileObject(
            name: $file->getFilename(),
            content: file_get_contents($file->path()),
            path: $file->path(),
            extension: $file->extension(),
            size: $file->getSize() ?? null,
        );
    }

    private static function isValidArray(array $file): void
    {
        if (! array_key_exists('base64', $file)) {
            throw new \Exception('The passed parameter does not contain a key with the name base64', 400);
        }
    }
}
