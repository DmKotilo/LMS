<?php

namespace App\Modules\City\src\Http\Resources;

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
use City\Models\City;
use Delivery\Http\Resources\DeliveryZoneResource;
use Delivery\Models\DeliveryZone;
use Delivery\Models\PickupLocation;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;

class CityResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */

    public static $model = City::class;




    public function toArray(Request $request): array
    {

        $hasPickupLocations = PickupLocation::where('city_id', $this->resource->id)->exists();

        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'position' => $this->position,
            'pickup_bonus_points' => $this->pickup_bonus_points,
            'delivery_zones' => DeliveryZoneResource::collection($this->deliveryZones),
//            'is_has_pickup' => $this->translate('name') == 'Другой город' ? false : true
            'is_has_pickup' => $hasPickupLocations
        ];
    }



}
