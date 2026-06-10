<?php

namespace App\Services;

class MarketingService
{
    public function createCourseAnnouncement(array $data): array
    {
        return [
            'banner_id' => 1,
            'status' => 'active',
        ];
    }
}
