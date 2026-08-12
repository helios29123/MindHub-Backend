<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'action_url' => $this->action_url,
            'channel' => $this->channel,
            'read_at' => $this->read_at ? $this->read_at->toDateTimeString() : null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
        ];
    }
}
