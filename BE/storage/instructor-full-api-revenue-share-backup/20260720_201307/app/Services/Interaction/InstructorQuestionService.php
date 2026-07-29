<?php

namespace App\Services\Interaction;

use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Interaction\InstructorQuestionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstructorQuestionService
{
    public function __construct(
        private readonly InstructorQuestionRepository $repository,
        private readonly InstructorCourseRepository $courseRepository
    ) {
    }

    public function paginateQuestions(int $instructorId, array $filters): LengthAwarePaginator
    {
        if (!empty($filters['course_id']) && ! $this->courseRepository->instructorOwnsCourse($instructorId, (int) $filters['course_id'])) {
            throw new NotFoundHttpException('Dữ liệu không hợp lệ.');
        }

        return $this->repository->paginateQuestions($instructorId, $filters);
    }
}
