<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningOutlineSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progresses = $this->additional['progresses'] ?? collect();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => (int) $this->sort_order,
            'lessons' => $this->lessons->map(function ($lesson) use ($progresses) {
                $lessonProgress = $progresses->get($lesson->id);
                return (new LearningOutlineLessonResource($lesson))
                    ->additional(['progress' => $lessonProgress]);
            }),
        ];
    }
}
