<?php

namespace Category\Http\Resources;

use Category\Models\MainCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainCategoryResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = MainCategory::class;
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'position' => $this->position,
            'category_id' => $this->category_id,
            'category_slug' => $this->category ? '/'.$this->category->slug_path : null,
            'name' => $this->name,
            'description' => $this->description,
            'text_color' => $this->text_color,
            'desktop_font_size' => $this->desktop_font_size,
            'mobile_font_size' => $this->mobile_font_size,
            'desktop_line_height' => $this->desktop_line_height,
            'mobile_line_height' => $this->mobile_line_height,
            'desktop_photo' => $this->desktop_photo ? url('/storage/'.$this->desktop_photo) : null,
            'mobile_photo' => $this->mobile_photo ? url('/storage/'.$this->mobile_photo) : null,

        ];
    }

}
