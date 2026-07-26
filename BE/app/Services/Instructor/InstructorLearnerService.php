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
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->paginateLearners($instructorId, $filters);
    }

    public function getLearnersSummary(int $instructorId, array $filters = []): array
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getLearnersSummary($instructorId, $filters);
    }

    public function getLearnersChart(int $instructorId, array $filters = []): array
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->getLearnersChart($instructorId, $filters);
    }

    public function getLearnerDetails(int $instructorId, int $enrollmentId): array
    {
        return $this->repository->getLearnerDetails($instructorId, $enrollmentId);
    }

    public function exportLearners(int $instructorId, array $filters = []): \Illuminate\Support\Collection
    {
        if (!empty($filters['course_id']) && $filters['course_id'] !== 'all' && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->exportLearners($instructorId, $filters);
    }
}