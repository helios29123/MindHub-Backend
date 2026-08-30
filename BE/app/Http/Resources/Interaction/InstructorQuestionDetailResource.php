<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorQuestionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $question = data_get($this->resource, 'question');
        $replies = data_get($this->resource, 'replies', []);
        $isAnswered = (bool) data_get($this->resource, 'is_answered', false);
        return [
            'comment_id' => (int) data_get($question, 'comment_id'),
            'content' => data_get($question, 'content'),
            'created_at' => data_get($question, 'created_at'),
            'learner' => [
                'id' => (int) data_get($question, 'learner_id'),
                'full_name' => data_get($question, 'learner_full_name'),
                'email' => data_get($question, 'learner_email'),
            ],
            'lesson' => [
                'id' => (int) data_get($question, 'lesson_id'),
                'title' => data_get($question, 'lesson_title'),
            ],
            'course' => [
                'id' => (int) data_get($question, 'course_id'),
                'title' => data_get($question, 'course_title'),
            ],
            'is_answered' => $isAnswered,
            'status_label' => $isAnswered ? 'Đã trả lời' : 'Chưa trả lời',

            'replies' => CommentReplyResource::collection($replies)->resolve($request),
        ];
    }
}