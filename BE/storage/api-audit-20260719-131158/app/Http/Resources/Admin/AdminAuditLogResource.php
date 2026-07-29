<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class AdminAuditLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'admin_id' => $this->admin_id, 'action' => $this->action, 'target_type' => $this->target_type, 'target_id' => $this->target_id, 'old_values' => $this->old_values, 'new_values' => $this->new_values, 'ip_address' => $this->ip_address, 'created_at' => $this->created_at];
    }
}
