<?php

namespace File\FileObjects;

use File\Exceptions\FileModuleException;
use File\Interfaces\FileObjectInterface;
use Illuminate\Support\Facades\Storage;

class FileObject implements FileObjectInterface
{
    /**
     * @var string variable from File\FileObjects\DiskConfiguration
     */
    private static ?string $disk = 'public';

    public function __construct(
        public string $name,
        public string $content,
        public string $path,
        public readonly string $extension,
        public readonly ?int $size = null,
    ) {
    }

    public function store(?string $directory = null, ?string $disk = null): bool
    {
        if ($disk) {
            self::setDisk($disk);
        }

        if ($directory) {
            $directory = $this->checkSlash($directory);

            // Убеждаемся что директория существует до записи файла
            Storage::disk(self::$disk)->makeDirectory(rtrim($directory, '/'), 0775, true);
        }
        $path = $directory.$this->hashName();

        try {
            Storage::disk(self::$disk)->put($path, $this->content);

            $this->path = $path;

            return true;
        } catch (\Throwable $th) {
            throw new FileModuleException('Failed to store file on disk or by extracting the absolute path of the file:'.$th->getMessage(), 400);
        }
    }

    /**
     * The function adds a slash if it is not in the parameter string
     */
    private function checkSlash(string $directory): ?string
    {
        if (substr($directory, -1) !== '/') {
            return $directory .= '/';
        }

        return $directory;
    }

    /**
     * Generating new filename
     */
    private function hashName(): string
    {
        return $this->name.'_'.uniqid().'.'.$this->extension;
    }

    public static function getDisk(): ?string
    {
        return self::$disk;
    }

    public static function setDisk(?string $disk): void
    {
        $diskConfig = new DiskConfiguration($disk);
        self::$disk = $diskConfig->getValue();
    }
}
