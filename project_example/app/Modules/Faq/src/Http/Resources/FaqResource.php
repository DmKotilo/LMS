<?php

namespace App\Modules\Faq\src\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Combo\Models\Combo;
use Faq\Models\Faq;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class FaqResource extends JsonResource
{

    public static $model = Faq::class;
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'button_text' => $this->translate('button_text'),
            'link_button' => $this->link_button,
            'position' => $this->position,
        ];
    }

}
