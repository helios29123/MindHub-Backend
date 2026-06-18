<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignedAssetUrlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->resource['url'],
            'expires_at' => $this->resource['expires_at'],
            'ttl_seconds' => $this->resource['ttl_seconds'],
            'file_type' => $this->resource['file_type'] ?? null,
            'file_size' => $this->resource['file_size'] ?? null,
        ];
    }
}
