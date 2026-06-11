<?php
namespace App\Services\Moderation;
use App\Repositories\Moderation\CourseModerationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class CourseModerationService
{
    public function __construct(
        private readonly CourseModerationRepository $courseModerationRepository
    ) {
    }
    public function getPendingCourses(array $filters): LengthAwarePaginator
    {
        return $this->courseModerationRepository->paginatePendingCourses($filters);
    }
}