<?php

namespace App\Http\Resources\Interaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment_id' => $this->id,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user_id,
                'full_name' => $this->user ? $this->user->full_name : null,
            ],
            'content' => $this->content,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toIsoString() : null,
        ];
    }
}
