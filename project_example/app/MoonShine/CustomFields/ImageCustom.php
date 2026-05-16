<?php

namespace App\MoonShine\CustomFields;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Fields\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\isInstanceOf;

class ImageCustom extends Image
{
    private const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB

/*    public function extraAttributes(Closure $callback): static
    {
        $callback =   fn(string $filename, int $index): ?FileItemExtra => new FileItemExtra(wide: false, auto: true, styles: 'width: 250px;');
        $this->parent::extraAttributes();
    }*/

    public function __construct(Closure|string|null $label = null, ?string $column = null, ?Closure $formatted = null)
    {
        parent::__construct($label, $column, $formatted);

        $this->setAttribute('accept', 'image/*');

        $this->removable()
            ->changeFill(function (Model $data, Image $field) use ($column) {
                if($data->$column instanceof Collection) {
                    return ($data->$column->pluck('path')->toArray());
                }
                return $data->$column?->path;
            })
            ->onApply(function (Model $data) {
                // block onApply
                return $data;
            })
            ->onAfterApply(function (Model $data, $values, Image $field) use ($column) {
                // $field->getRemainingValues(); values that remained in the form taking into account deletions
                // $field->toValue(); current images
                // $field->toValue()->diff($field->getRemainingValues()) deleted images

                if ($values) {
                    if(!is_array($values)) $values = [$values];

                    // Проверяем размер загружаемых файлов и выводим понятную ошибку
                    foreach ($values as $value) {
                        if ($value instanceof UploadedFile && $value->getSize() > self::MAX_FILE_SIZE_BYTES) {
                            throw ValidationException::withMessages([
                                $column => ['Размер файла не должен превышать 10 МБ'],
                            ]);
                        }
                    }

                    // Если поле не multiple — удаляем старые файлы текущего relation
                    if($this->multiple == false) {
                        $item = $data->$column;
                        if($item) {
                            $item->delete();
                        }
                    }

                    foreach ($values as $value) {
                        $file = $data->makeFile($value);
                        $className = class_basename($data);
                        $data->selectFileRelation($column)->storeFile(
                            $file,
                            "$className/$data->id/image",
                            $column,
                        );
                    }
                }

/*                if ($values) {
                    if(!is_array($values)) $values = [$values];
                    foreach ($values as $value) {
                        $files = [$data->makeFile($values)];
                        $image = $data->$column;
                        $className = class_basename($data);
                        if (!$image) {
                            $image = $data->selectFileRelation($column)->storeFile(
                                $files[0],
                                "$className/$data->id/image",
                            );
                        } else {
                            $image->updateFile(
                                $files[0],
                                "$className/$data->id/image",
                            );
                            $image->refresh();
                        }
                    }
                }*/


                foreach (collect($field->toValue())->diff($field->getRemainingValues()) as $removed)  {
                    $normalized = static function ($value) {
                        // В форме может прийти полный URL, /storage/... или относительный путь
                        if (is_array($value) && isset($value['path'])) {
                            $value = $value['path'];
                        }
                        $value = (string) $value;
                        $value = str_replace(['/storage/', 'storage/'], '', $value);
                        // В БД хранится относительный путь; дополнительно ищем по basename
                        return $value;
                    };

                    $path = $normalized($removed);
                    $basename = basename($path);

                    $data->$column()
                        ->where(fn($q) =>
                            $q->where('path', $path)
                              ->orWhere('path', 'like', "%{$basename}")
                        )
                        ->first()?->delete();
                }

                return $data;
            })
            ->onAfterDestroy(function (Model $data, mixed $values, Image $field) use ($column) {
/*                $data->$column?->delete();*/
                return $data;
            });
    }
}
