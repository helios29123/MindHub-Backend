<?php
namespace App\Http\Resources\Interaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'review_id' => $this->id,
            'course_id' => $this->order?->course_id,
            'rating' => $this->rating,
        ];
    }
}