<?php

namespace App\Services\Report;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Report\InstructorEnrollmentChartRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorEnrollmentChartService
{
    public function __construct(
        private readonly InstructorEnrollmentChartRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function getChart(int $instructorId, array $filters): array
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getChart($instructorId, $filters);
    }
}