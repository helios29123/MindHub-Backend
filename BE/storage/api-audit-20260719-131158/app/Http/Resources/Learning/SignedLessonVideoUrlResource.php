<?php
namespace App\Http\Resources\Learning;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class SignedLessonVideoUrlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stream_url' => $this->resource['stream_url'] ?? null,
            'expires_in' => (int) ($this->resource['expires_in'] ?? 0),
            'expires_at' => $this->resource['expires_at'] ?? null,
        ];
    }
}