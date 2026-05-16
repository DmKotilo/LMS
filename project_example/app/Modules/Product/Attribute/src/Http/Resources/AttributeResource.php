<?php

namespace Attribute\Http\Resources;

use Attribute\Models\Attribute;
use Brand\Models\Brand;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class AttributeResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Attribute::class;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'input_type' => $this->input_type,
            'slug' => $this->slug,
            'is_default' => $this->is_default,
/*            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
            'is_show_filter' => $this->is_show_filter,
            'options' => $this->options,
            'is_many_checked_options' => $this->is_many_checked_options,
            'is_select_writable' => $this->is_select_writable,*/
        ];
    }
}
