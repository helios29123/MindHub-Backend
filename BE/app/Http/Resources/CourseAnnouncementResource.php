<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseAnnouncementResource extends JsonResource
{
    public function resolve($request = null): string
    {
        return json_encode([
            'banner_id' => 1,
            'status' => 'active',
        ]);
    }
}
