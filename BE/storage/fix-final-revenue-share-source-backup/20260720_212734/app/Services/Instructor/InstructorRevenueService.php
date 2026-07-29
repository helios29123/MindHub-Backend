<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorRevenueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorRevenueService
{
    public function __construct(
        private readonly InstructorRevenueRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function getRevenueReport(int $instructorId, array $filters): array
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getRevenueReport($instructorId, $filters);
    }
}