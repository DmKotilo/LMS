<?php

namespace App\Modules\Review\src\Http\Resources;

use App\Modules\Review\src\Enums\ReviewStatusEnum;
use Attribute\Models\Attribute;
use Brand\Models\Brand;
use File\Http\Resources\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Product\Models\Product;
use Review\Models\Review;
use User\Models\User;
use function Laravel\Prompts\text;

class ReviewResource extends JsonResource
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
        $authUser = auth()->user();

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
        $reviewAnswer = $this->answer()->where('is_active',true)->first();

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
            'rating' => (int) $this->rating,
            'item_type' => $this->item_type,
            'name' => $itemName,
            'item_id' => $this->item_id,
            'slug' => $this->item->slug ? $this->item->slug : null,
            'product_images' => new FileResource($this->item->firstImage),
            'status' => $this->status->toString(),
            'userName' => $fullName,
            'user_logo' => new FileResource($this->avatar),
            'images' => FileResource::collection($this->images),
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),
            'answer' => $reviewAnswer ? html_entity_decode(strip_tags($reviewAnswer->text)) : null,
            'likes_count' => $this->likesCount(),
            'liked_by_user' => $authUser ? $this->likedByUser($authUser->id) : false,
            'disliked_by_user' => $authUser ? $this->dislikedByUser($authUser->id) : false,

            'bonus_awarded' => $this->status->value === ReviewStatusEnum::Approved->value,
        ];
    }
}
