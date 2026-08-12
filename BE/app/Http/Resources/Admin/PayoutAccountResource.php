<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class PayoutAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'user' => ['id' => $this->user?->id, 'name' => $this->user?->name ?? $this->user?->full_name, 'email' => $this->user?->email], 'provider' => $this->provider, 'bank_name' => $this->bank_name ?? $this->provider_name ?? null, 'account_number_masked' => $this->account_number ? str_repeat('*', max(0, strlen($this->account_number) - 4)) . substr($this->account_number, -4) : null, 'account_name' => $this->account_name, 'status' => $this->status, 'is_default' => (bool)($this->is_default ?? false), 'connected_at' => $this->connected_at ?? null, 'verified_at' => $this->verified_at ?? null, 'updated_at' => $this->updated_at];
    }
}
