<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\File;


use App\MoonShine\CustomFields\FileCustom;
use App\MoonShine\CustomFields\ImageCustom;
use File\Models\Files\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<File>
 */
class FileResource extends ModelResource
{
    protected string $model = File::class;
    protected bool $isAsync = false;
    protected string $title = 'Файлы блога';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable{
        return [
            Text::make('Название','file_name')->required()->sortable(),
            \MoonShine\UI\Fields\File::make('Файл', 'path'),
            Text::make('Extension','extension')->default('not data')->required(),
            Text::make('Disk','disk')->default('public')->required(),
            Number::make('Размер','size')->default(null),
        ];
    }
    protected function detailFields(): iterable{
        return [
            Text::make('Название','file_name')->required()->sortable(),
            \MoonShine\UI\Fields\File::make('Файл', 'path'),
            Text::make('Extension','extension')->default('not data')->required(),
            Text::make('Disk','disk')->default('not data')->required(),
            Number::make('Размер','size')->default(null),
        ];
    }
    protected function formFields(): iterable{
        return [
            Box::make([
                Text::make('Название','file_name')->required()->sortable(),
//                \MoonShine\UI\Fields\File::make('Images', 'files')
//                    ->multiple()
//                    ->removable()
//                    ->changeFill(function (Model $data, \MoonShine\UI\Fields\File $field) {
//                        // return $data->images->pluck('file');
//                        // или raw
//                        return DB::table('files')->pluck('path');
//                    })
//                    ->onApply(function (Model $data): Model {
//                        // блокируем onApply
//                        return $data;
//                    })
//
//                    ->onAfterApply(function (Model $data, false|array $values, \MoonShine\UI\Fields\File $field)  {
//                        dd($this);// $field->getRemainingValues(); значения, которые остались в форме с учетом удалений
//                        // $field->toValue(); текущие изображения
//                        // $field->toValue()->diff($field->getRemainingValues()) удаленные изображения
//                        if ($values) {
//                            $files = [$data->makeFile($values)];
//                            $image = $data->image;
//                            $className = class_basename($data);
//                            if (!$image) {
//
//                                $image = $data->selectFileRelation($column)->storeFile(
//                                    $files[0],
//                                    "$className/$data->id/image",
//                                );
//                            } else {
//                                $image->updateFile(
//                                    $files[0],
//                                    "$className/$data->id/image",
//                                );
//                                $image->refresh();
//                            }
//
//                        }
//
////                        if($values !== false) {
////                            foreach ($values as $value) {
////
////                                DB::table('files')->insert([
////                                    'file' => $field->getApplyClass()->store($field, $value),
////                                    // or 'file' => $value->store(),
////                                ]);
////                            }
////                        }
//
//                        foreach ($field->toValue()->diff($field->getRemainingValues()) as $removed) {
//                            DB::table('files')->where('path', $removed)->delete();
//                            Storage::disk('public')->delete($removed);
//                        }
//
//                        // или $field->removeExcludedFiles();
//
//                        return $data;
//                    })
//                    ->onAfterDestroy(function (Model $data, mixed $values, \MoonShine\UI\Fields\File $field) {
//                        foreach ($values as $value) {
//                            Storage::disk('public')->delete($value);
//                        }
//
//                        return $data;
//                    }),

                ImageCustom::make('photo','file'),
                Text::make('Extension','extension')->default('not data')->required(),
                Text::make('Disk','disk')->default('not data')->required(),
                Number::make('Размер','size')->default(null),
            ]),
        ];
    }

    /**
     * @param File $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */

    protected function rules(mixed $item): array
    {
        return [];
    }
}
