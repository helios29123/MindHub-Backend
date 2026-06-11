<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    public function resolve($request = null): string
    {
        $status = $this->resource instanceof \App\Models\Comment 
            ? $this->resource->status 
            : ($this->resource->deleted_at ? 'deleted' : 'visible');

        return json_encode([
            'id' => $this->resource->id,
            'status' => $status,
        ]);
    }
}
