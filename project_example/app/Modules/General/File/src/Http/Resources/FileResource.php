<?php

namespace File\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\ResponseField;

class FileResource extends JsonResource
{
/*    #[ResponseField('data', 'array')]
    #[ResponseField('data.fileable_type', 'string')]
    #[ResponseField('data.fileable_id', 'integer')]
    #[ResponseField('data.file_name', 'string')]
    #[ResponseField('data.path', 'string')]
    #[ResponseField('data.extension', 'string')]
    #[ResponseField('data.size', 'integer')]
    #[ResponseField('data.disk', 'string')]*/
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'path' => Storage::disk('public')
                ->url($this->path),
/*            'extension' => $this->extension,
            'size' => $this->size,
            'disk' => $this->disk,*/
        ];
    }
}
