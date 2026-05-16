<?php

namespace Catalog\Http\Resources;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Product\Attribute\src\Http\Resources\ProductAttributeResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewResource;
use App\Modules\Product\Tag\src\Http\Resources\ProductTagResource;
use App\Modules\Product\Tag\src\Http\Resources\TagResource;
use Attribute\Http\Resources\AttributeResource;
use Brand\Http\Resources\BrandResource;
use Cart\Models\UserCart;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Product\Models\Product;

class ProductCardCollection extends ResourceCollection
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

/*    public function __construct($resource, $haveReturnGames = false, $sorted = false, $includeReturn = false)
    {
        if(method_exists($resource, 'total')) {
            $this->pagination = [
                'total' => $resource->total(),
                'count' => $resource->count(),
                'per_page' => $resource->perPage(),
                'current_page' => $resource->currentPage(),
                'total_pages' => $resource->lastPage()
            ];
            $resource = $resource->getCollection();
        }

        $this->haveReturnGames = $haveReturnGames;
        $this->sorted = $sorted;
        $this->includeReturn = $includeReturn;
        parent::__construct($resource);
    }*/

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'current_page' => $this->currentPage(),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
            'data' => $this->collection,
        ];
    }

}
