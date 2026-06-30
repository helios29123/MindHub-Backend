<?php
namespace App\Http\Resources\Learning;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class WatermarkInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'text' => (string) ($this->resource['text'] ?? ''),
            'mode' => (string) ($this->resource['mode'] ?? 'moving'),
            'opacity' => (float) ($this->resource['opacity'] ?? 0.25),
            'refresh_seconds' => (int) ($this->resource['refresh_seconds'] ?? 30),
            'position_rule' => (string) ($this->resource['position_rule'] ?? 'random'),
            'generated_at' => $this->resource['generated_at'] ?? null,
        ];
    }
}