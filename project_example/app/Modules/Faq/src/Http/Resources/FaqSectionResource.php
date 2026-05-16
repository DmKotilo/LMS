<?php

namespace App\Modules\Faq\src\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Traits\GetCart;
use App\Modules\Faq\src\Models\FaqSection;
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

class FaqSectionResource extends JsonResource
{

    public static $model = FaqSection::class;
    public function toArray($request)
    {

        return [
            'section_id' => $this->id,
            'section_name' => $this->translate('name'),
            'faqs' => FaqResource::collection($this->faqs()->orderBy('position')->get()),
        ];
    }

}
