<?php

namespace App\Repositories\Instructor;

use App\Models\InstructorProfile;

class InstructorProfileRepository
{
    public function create(array $data): InstructorProfile
    {
        return InstructorProfile::create($data);
    }
}
