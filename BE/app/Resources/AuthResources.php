<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AuthResources extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->resource['access_token'] ?? null,
            'refresh_token' => $this->resource['refresh_token'] ?? null,
            'expires_in' => $this->resource['expires_in'] ?? null,
            'user' => new UserResources($this->resource['user']),
        ];
    }
}
