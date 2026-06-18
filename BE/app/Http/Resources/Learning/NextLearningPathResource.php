<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NextLearningPathResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'level' => $this->level,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'path_reason' => $this->when(isset($this->path_reason), $this->path_reason),
            'category' => $this->whenLoaded('categories', function () {
                $category = $this->categories->first();
                return $category ? [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null;
            }),
        ];
    }
}
