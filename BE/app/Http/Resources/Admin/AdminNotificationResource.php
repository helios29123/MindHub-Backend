<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class AdminNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'type' => $this->type, 'title' => $this->title, 'message' => $this->message, 'severity' => $this->severity, 'related_type' => $this->related_type, 'related_id' => $this->related_id, 'data' => $this->data, 'is_read' => (bool)$this->is_read, 'read_at' => $this->read_at, 'created_at' => $this->created_at];
    }
}
