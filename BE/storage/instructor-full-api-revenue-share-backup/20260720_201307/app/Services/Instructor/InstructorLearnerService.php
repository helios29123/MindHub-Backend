<?php

namespace App\Services\Instructor;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorLearnerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorLearnerService
{
    public function __construct(
        private readonly InstructorLearnerRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function paginateLearners(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->paginateLearners($instructorId, $filters);
    }
}