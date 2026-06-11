<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "full_name" => $this->full_name,
            "email" => $this->email,
            "phone" => $this->phone,
            "role" => $this->role,
            "status" => $this->status,
            "oauth_account_login" => (bool) $this->oauth_account_login,
            "email_verified_at" => $this->email_verified_at?->toIsoString(),
            "last_login_at" => $this->last_login_at?->toIsoString(),
            "locked" => (bool) $this->locked,
            "locked_reason" => $this->locked_reason,
            "created_at" => $this->created_at?->toIsoString(),
            "updated_at" => $this->updated_at?->toIsoString(),
        ];
    }
}
