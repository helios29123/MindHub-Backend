<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "course_id" => $this->course_id,
            "title" => $this->title,
            "description" => $this->description,
            "sort_order" => $this->sort_order,
            "status" => $this->status,
            "created_at" => $this->created_at?->toIsoString(),
            "updated_at" => $this->updated_at?->toIsoString(),

            "course" => $this->whenLoaded("course", function () {
                return [
                    "id" => $this->course->id,
                    "title" => $this->course->title,
                    "slug" => $this->course->slug,
                    "status" => $this->course->status,
                ];
            }),
        ];
    }
}
