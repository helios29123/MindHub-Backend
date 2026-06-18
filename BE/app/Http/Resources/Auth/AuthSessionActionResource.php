<?php
namespace App\Http\Resources\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class AuthSessionActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'revoked_count' => (int) ($this->resource['revoked_count'] ?? 0),
        ];
    }
}