<?php

declare(strict_types=1);

namespace App\MoonShine\CustomFields;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MoonShine\UI\Fields\Image;
use Product\Models\Product;

class ProductVideoCustom extends Image
{
    protected string $accept = 'video/*';

    public function __construct(Closure|string|null $label = null, ?string $column = null, ?Closure $formatted = null)
    {
        parent::__construct($label, $column, $formatted);

        $this->setAttribute('accept', $this->accept)
            ->multiple()
            ->removable()
            ->reorderable(fn($ctx) => '/sort/reorder-videos/' . base64_encode($ctx->getResource()::class) . '/' . $ctx->getData()->getKey())
            ->changeFill(function (Model $data, $field) use ($column) {
                // ожидаем коллекцию файлов
                if ($data->$column instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $data->$column->sortBy('position')->pluck('path')->toArray();
                }
                return $data->$column?->path;
            })
            ->onApply(fn() => null)
            ->onAfterApply(function (Model $item, $values, $field) use ($column) {
                // Удаление
                $currentPaths = collect($field->toValue());
                $remainingPaths = collect($field->getRemainingValues());
                $deletedPaths = $currentPaths->diff($remainingPaths);

                foreach ($deletedPaths as $path) {
                    $video = $item->$column()->where('path', $path)->first();
                    if (!$video) continue;

                    if ($video->preview) {
                        $previewFile = $video->preview->preview;
                        $video->preview->delete();
                        $previewFile?->delete();
                    }
                    $video->delete();
                }

                // Добавление новых: назначаем position = max(position) + 1
                if ($values) {
                    foreach ($values as $file) {
                        if (!$file instanceof \Illuminate\Http\UploadedFile) continue;

                        $path = $file->store('videos', 'public');

                        $maxPosition = \File\Models\Files\File::where('fileable_type', Product::class)
                            ->where('fileable_id', $item->id)
                            ->max('position') ?? 0;

                        $item->videos()->create([
                            'fileable_type' => Product::class,
                            'fileable_id'   => $item->id,
                            'type_relation' => 'videos',
                            'file_name'     => $file->getClientOriginalName(),
                            'path'          => $path,
                            'extension'     => $file->getClientOriginalExtension(),
                            'size'          => $file->getSize(),
                            'disk'          => 'public',
                            'position'      => $maxPosition + 1,
                        ]);
                    }
                }

                return $item;
            });
    }
}
