<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "bio" => $this->bio,
            "expertise" => $this->expertise,
            "experience_years" => $this->experience_years,
            "level" => $this->level,
            "user" => $this->whenLoaded("user", function () {
                return [
                    "id" => $this->user->id,
                    "full_name" => $this->user->full_name,
                    "email" => $this->user->email,
                    "phone" => $this->user->phone,
                    "role" => $this->user->role,
                    "status" => $this->user->status,
                ];
            }),
        ];
    }
}
