<?php

namespace App\Modules\UserSetting\src\Http\Resources;

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
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use UserSetting\Models\UserSetting;

class UserSettingResource extends JsonResource
{
    use GetCart;
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = UserSetting::class;
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'comment_notifications' => $this->comment_notifications,
            'sale_notifications' => $this->sale_notifications,
            'email_notifications' => $this->email_notifications,
            'sms_notifications' => $this->sms_notifications,
            'messenger_notifications' => $this->messenger_notifications,
            'favorite_categories' => $this->favorite_categories,
            'often_type' => $this->often_type?->value,
            'favorite_categories_list' => $this->favoriteCategories?->pluck('id')->toArray(),
        ];
    }

}
