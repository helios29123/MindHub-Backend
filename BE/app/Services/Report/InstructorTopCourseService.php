<?php

namespace App\Services\Report;

use App\Repositories\Report\InstructorTopCourseRepository;

class InstructorTopCourseService
{
    public function __construct(
        private readonly InstructorTopCourseRepository $repository
    ) {
    }

    public function getTopCourses(int $instructorId, array $filters): array
    {
        return $this->repository->getTopCourses($instructorId, $filters);
    }
}