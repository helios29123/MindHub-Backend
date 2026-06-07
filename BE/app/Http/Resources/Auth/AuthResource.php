<?php
namespace App\Http\Resources\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token_type' => $this->resource['token_type'] ?? 'Bearer',
            'access_token' => $this->resource['access_token'] ?? null,
            'refresh_token' => $this->resource['refresh_token'] ?? null,
            'expires_in' => $this->resource['expires_in'] ?? null,
            'user' => new UserResource($this->resource['user']),
        ];
    }
}
