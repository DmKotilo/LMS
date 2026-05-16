<?php

namespace App\Modules\Comment\src\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use File\Http\Resources\FileResource;
use User\Models\User;


class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->whenLoaded('user');
        $authUser = auth()->user();

        $fullName = '';
        if ($user) {
            if ($user->name && $user->last_name) {
                $fullName = $user->name . ' ' . mb_substr($user->last_name, 0, 1) . '.';
            } elseif ($user->name) {
                $fullName = $user->name;
            } elseif ($user->last_name) {
                $fullName = $user->last_name;
            }
        }

         $userAvatar = null;
         if ($user && $user->relationLoaded('avatar')) {
             $userAvatar = new FileResource($user->avatar);
         }

        return [
            'id' => $this->id,
            'text' => $this->text,
             'status' => $this->status,
            'created_at' => \Carbon\Carbon::parse($this->created_at)->translatedFormat('d F Y'),
            'userName' => $fullName,
             'user_logo' => $userAvatar,
            'likes_count' => $this->likesCount(),
            'dislikes_count' => $this->dislikesCount(),
            'liked_by_user' => $authUser ? $this->likedByUser($authUser->id) : false,
            'disliked_by_user' => $authUser ? $this->dislikedByUser($authUser->id) : false,
        ];
    }
}
