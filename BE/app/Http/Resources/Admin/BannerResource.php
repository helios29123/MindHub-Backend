<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'banner_id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'target_url' => $this->target_url,
            'position' => $this->position,
            'sort_order' => $this->sort_order !== null ? (int) $this->sort_order : null,
            'start_at' => $this->start_at ? $this->start_at->toIsoString() : null,
            'end_at' => $this->end_at ? $this->end_at->toIsoString() : null,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toIsoString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIsoString() : null,
        ];
    }
}
