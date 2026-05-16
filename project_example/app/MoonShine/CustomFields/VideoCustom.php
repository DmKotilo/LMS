<?php

namespace App\MoonShine\CustomFields;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use MoonShine\UI\Fields\File;

class VideoCustom extends File
{
    protected int $maxSize; // байты

    public function __construct(Closure|string|null $label = null, ?string $column = null, ?Closure $formatted = null)
    {
        parent::__construct($label, $column, $formatted);

        $this->setAttribute('accept', 'video/*');
        $this->allowedExtensions(['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v']);

        $uploadMax = $this->parsePhpSize(ini_get('upload_max_filesize'));
        $postMax = $this->parsePhpSize(ini_get('post_max_size'));
        $this->maxSize = min($uploadMax, $postMax);

        $this->removable()
            ->changeFill(function (Model $data, File $field) use ($column) {
                if ($data->$column instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $data->$column->first()?->path ?? null;
                }
                return $data->$column?->path ?? null;
            })
            ->onApply(function (Model $data) {
                return $data;
            })
            ->onAfterApply(function (Model $data, $values, File $field) use ($column) {

                if (!$values) {
                    return $data;
                }

                if (is_array($values)) {
                    $values = $values[0];
                }

                // проверка размера файла
                if ($values instanceof UploadedFile && $values->getSize() > $this->maxSize) {
                    $sizeMb = round($this->maxSize / 1024 / 1024, 2);
                    throw ValidationException::withMessages([
                        $column => "Файл слишком большой. Максимальный размер: {$sizeMb} МБ",
                    ]);
                }

                $current = $data->$column()->first();
                if ($current) {
                    $current->delete();
                }

                $file = $data->makeFile($values);
                $className = class_basename($data);

                $data->selectFileRelation($column)->storeFile(
                    $file,
                    "$className/$data->id/video",
                    $column,
                );

                return $data;
            })
            ->onAfterDestroy(function (Model $data, mixed $values, File $field) use ($column) {
                return $data;
            });
    }

    /**
     * Конвертирует размер из php.ini в байты
     */
    protected function parsePhpSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $bytes = (int) $size;

        return match ($unit) {
            'g' => $bytes * 1024 * 1024 * 1024,
            'm' => $bytes * 1024 * 1024,
            'k' => $bytes * 1024,
            default => $bytes,
        };
    }
}
