<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'slug' => $this->slug,
            'type' => $this->type,
            'thumbnail_url' => $this->thumbnail_url ?? null,
            'price' => isset($this->price) ? (float) $this->price : null,
            'sale_price' => isset($this->sale_price) ? (float) $this->sale_price : null,
            'instructor_name' => $this->instructor_name ?? null,
        ];
    }
}
