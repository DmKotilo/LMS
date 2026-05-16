<?php

namespace File\Http\Controllers;

//TODO: Test
use File\Http\Requests\TestFormRequest;
use File\Models\Files\File;
use File\Models\VideoPreview;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Файлы ТЕСТ')]
class FileController
{
    #[Endpoint('Загрузить файл из ФормДаты')]
    #[BodyParam('file', 'file')]
    public function buildFileObjectFromFormData(TestFormRequest $request)
    {
        $files = $request->fileObject('file');

        return response()->json(get_class($files[0]));
    }

    #[Endpoint('Загрузить файл из base64 (Массив)')]
    #[BodyParam('file', 'array')]
    public function buildFileObjectFromBase64(TestFormRequest $request)
    {
        $files = $request->fileObject('file');

        return response()->json(get_class($files[0]));
    }

    #[Endpoint('Сортировка изображений')]
    #[BodyParam('data', 'string')]
    public function reorderImages(Request $request, string $encodedModel, int $id)
    {
        $modelClass = base64_decode($encodedModel);

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $orderColumn = File::where('fileable_type', $modelClass)
            ->where('fileable_id', $id)
            ->whereNotNull('position')
            ->exists() ? 'position' : 'id';

        $files = File::where('fileable_type', $modelClass)
            ->where('fileable_id', $id)
            ->orderBy($orderColumn)
            ->get()
            ->values();

        $indexes = explode(',', $request->input('data'));

        foreach ($indexes as $newPosition => $oldIndex) {
            if (isset($files[$oldIndex])) {
                $files[$oldIndex]->update(['position' => $newPosition]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    #[Endpoint('Сортировка видео')]
    #[BodyParam('data', 'string|array')]
    public function reorderVideos(Request $request, string $encodedModel, int $id)
    {
        $modelClass = base64_decode($encodedModel);

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $files = File::where('fileable_type', $modelClass)
            ->where('fileable_id', $id)
            ->where('type_relation', 'videos')
            ->orderBy('position')
            ->get()
            ->values();

        $indexes = $request->input('data');
        if (is_string($indexes)) {
            $indexes = explode(',', $indexes);
        }

        if (!is_array($indexes)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }

        foreach ($indexes as $newPosition => $oldIndex) {
            if (isset($files[$oldIndex])) {
                $files[$oldIndex]->update(['position' => $newPosition + 1]);
            }
        }

        return response()->json(['status' => 'ok']);
    }


}
