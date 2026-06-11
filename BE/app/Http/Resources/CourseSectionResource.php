<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasAccess = $this->additional['has_access'] ?? false;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => (int) $this->sort_order,
            'lessons' => $this->whenLoaded('lessons', function () use ($hasAccess) {
                return LessonResource::collection($this->lessons)->map(function ($lessonResource) use ($hasAccess) {
                    return $lessonResource->additional(['has_access' => $hasAccess]);
                });
            }),
        ];
    }
}
