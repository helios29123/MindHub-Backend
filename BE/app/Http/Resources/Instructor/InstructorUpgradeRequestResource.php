<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorUpgradeRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'application_status' => $this->resource['application_status'] ?? null,
            'submitted_at' => $this->resource['submitted_at'] ?? null,
            'review_note' => $this->resource['review_note'] ?? null,

            'user' => $this->resource['user'] ?? null,
            'instructor_profile' => $this->resource['instructor_profile'] ?? null,
            'payout_account' => $this->resource['payout_account'] ?? null,
        ];
    }
}
