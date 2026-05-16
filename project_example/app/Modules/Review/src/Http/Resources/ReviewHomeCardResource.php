<?php

namespace App\Modules\Review\src\Http\Resources;

use Attribute\Models\Attribute;
use Brand\Models\Brand;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Review\Models\ProductReview;
use Review\Models\Review;
use User\Models\User;

class ReviewHomeCardResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = Review::class;
    public function toArray(Request $request): array
    {
        $user = User::find($this->user_id);


        $fullName = '';
        if ($user) {
            if ($user->name && $user->last_name) {
                $fullName = $user->name . ' ' . mb_substr($user->last_name, 0, 1);
            } elseif ($user->name) {
                $fullName = $user->name;
            } elseif ($user->last_name) {
                $fullName = $user->last_name;
            }
        }
        $itemName = null;
        if ($this->item) {

            if ($this->item && $this->item->getAttribute('name')) {
                $itemName = $this->item->name;
            } elseif ($this->item && $this->item->getAttribute('title')) {
                $itemName = $this->item->title;
            }

        }

        return [
            'id' => $this->id,
            'text' => $this->text,
            'item_type' => $this->item_type,
            'rating' => $this->rating,
            'product_images' => $this->item ? new FileResource($this->item->firstImage) : null,
            'name' => $itemName,
            'userName' => $fullName,
            'user_logo' => new FileResource($this->avatar),
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),
        ];
    }
}
