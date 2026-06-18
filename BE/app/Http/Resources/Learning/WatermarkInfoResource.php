<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WatermarkInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'text' => $this->resource['text'],
            'mode' => $this->resource['mode'],
            'opacity' => $this->resource['opacity'],
            'refresh_seconds' => $this->resource['refresh_seconds'],
            'position_rule' => $this->resource['position_rule'],
            'generated_at' => $this->resource['generated_at'],
        ];
    }
}
