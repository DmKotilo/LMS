<?php

namespace Category\Http\Resources;

use Brand\Models\Brand;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Setting\Models\Setting;

class CategoryItemTreeResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = Category::class;
    public function toArray(Request $request): array
    {
        $secondItemPercent = null;
        if ($this->has_second_item_discount) {
            $value = Setting::where('key', 'second_item_discount_percent')->value('value');
            $secondItemPercent = is_null($value) ? 10 : (int) $value;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'path' => $this->slug_path,
            'icon_path' => $this->image?->path,
            'parent' => CategoryItemTreeResource::make($this->parent),
            'second_item_percents' => $secondItemPercent,
        ];
    }
}
