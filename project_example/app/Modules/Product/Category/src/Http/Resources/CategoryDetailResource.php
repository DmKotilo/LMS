<?php

namespace Category\Http\Resources;

use Brand\Models\Brand;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class CategoryDetailResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Category::class;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'explanation' => $this->explanation,
            'icon_path' => $this->image?->path,
            'parent' => CategoryDetailResource::make($this->parent),
            'description' => $this->description,
        ];
    }
}
