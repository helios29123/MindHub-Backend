<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuleBasedRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->when(isset($this->short_description), $this->short_description),
            'thumbnail_url' => $this->when(isset($this->thumbnail_url), $this->thumbnail_url),
            'level' => $this->level,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'score' => $this->when(isset($this->score), $this->score),
            'reasons' => $this->when(isset($this->reasons), $this->reasons),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
            }),
        ];
    }
}
