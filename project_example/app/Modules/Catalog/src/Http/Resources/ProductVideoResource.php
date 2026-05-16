<?php

namespace App\Modules\Catalog\src\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductVideoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'video_url' => Storage::url($this->path),
            'position' => (int) $this->position,
            'preview' => $this->preview?->preview ? [
                'id' => $this->preview->preview->id,
                'url' => Storage::url($this->preview->preview->path),
                'file_name' => $this->preview->preview->file_name,
            ] : null,
        ];
    }
}
