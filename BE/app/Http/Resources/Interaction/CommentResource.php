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
                'full_name' => $this->user ? ($this->user->full_name ?? $this->user->name) : null,
                'role' => $this->user ? ($this->user->role ?? 'learner') : 'learner',
                'avatar' => $this->user ? ($this->user->avatar ?? null) : null,
            ],
            'content' => $this->content,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toIsoString() : null,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
