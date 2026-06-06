<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserResources extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'email_verified_at' => optional($this->email_verified_at)->toISOString(),
            'last_login_at' => optional($this->last_login_at)->toISOString(),
            'locked_at' => optional($this->locked_at)->toISOString(),
            'locked_reason' => $this->locked_reason,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
