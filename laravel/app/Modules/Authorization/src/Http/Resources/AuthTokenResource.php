<?php

namespace Authorization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use User\Http\Resources\UserResource;

class AuthTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'token_type' => $this->resource['token_type'],
            'default_path' => $this->resource['default_path'],
            'user' => new UserResource($this->resource['user']),
        ];
    }
}
