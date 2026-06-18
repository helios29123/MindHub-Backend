<?php
namespace App\Http\Resources\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tokenData = is_array($this->resource) ? $this->resource : [];
        return [
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'access_token' => $tokenData['access_token'] ?? null,
            'refresh_token' => $this->when(
                array_key_exists('refresh_token', $tokenData),
                $tokenData['refresh_token'] ?? null
            ),
            'expires_in' => $this->when(
                array_key_exists('expires_in', $tokenData),
                $tokenData['expires_in'] ?? null
            ),
        ];
    }
}