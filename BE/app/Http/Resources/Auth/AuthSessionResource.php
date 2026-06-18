<?php
namespace App\Http\Resources\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class AuthSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'device_name' => $this->device_name,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'status' => $this->resolveSessionStatus(),
            'expires_at' => $this->expires_at?->toJSON(),
            'revoked_at' => $this->revoked_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
        ];
    }
    private function resolveSessionStatus(): string
    {
        if ($this->revoked_at !== null) {
            return 'revoked';
        }
        if ($this->expires_at !== null && $this->expires_at->lessThanOrEqualTo(now())) {
            return 'expired';
        }
        return 'active';
    }
}