<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorRevenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->resource['items'] ?? [],
            'summary' => $this->resource['summary'] ?? [],
            'filters' => $this->resource['filters'] ?? [],
            'meta' => $this->resource['meta'] ?? [],
        ];
    }
}