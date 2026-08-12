<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class AdminDashboardResource extends JsonResource
{
    public function toArray($request): array
    {
        return (array)$this->resource;
    }
}
