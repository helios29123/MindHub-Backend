<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class CommentReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUserId = $request->user()?->id;
        $userId = (int) data_get($this->resource, 'user_id');
        return [
            'id' => (int) data_get($this->resource, 'id'),
            'parent_id' => (int) data_get($this->resource, 'parent_id'),
            'lesson_id' => (int) data_get($this->resource, 'lesson_id'),
            'content' => data_get($this->resource, 'content'),
            'status' => data_get($this->resource, 'status'),
            'created_at' => data_get($this->resource, 'created_at'),
            'user' => [
                'id' => $userId,
                'full_name' => data_get($this->resource, 'user_full_name'),
                'role' => data_get($this->resource, 'user_role'),
            ],
            'is_instructor_reply' => $authUserId !== null && $userId === (int) $authUserId,
        ];
    }
}