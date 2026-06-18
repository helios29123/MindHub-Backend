<?php

namespace App\Services\Course;

use App\Models\Course;
use App\Repositories\Course\CoursePublicRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RelatedCourseService
{
    public function __construct(
        private readonly CoursePublicRepository $repository
    ) {
    }

    public function getRelatedCourses(int $courseId, array $filters): array|LengthAwarePaginator
    {
        $currentCourse = $this->repository->findPublicCourseById($courseId);

        if (!$currentCourse) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy khóa học.', 404);
        }

        $query = $this->repository->getRelatedPublicCoursesBaseQuery($currentCourse);
        $candidates = $query->get();

        $scoredCandidates = $this->scoreCandidates($currentCourse, $candidates);

        // Sort: score desc, rating desc, published_at desc, id desc
        $sortedCandidates = $scoredCandidates->sortBy([
            ['score', 'desc'],
            ['rating_avg', 'desc'],
            ['published_at', 'desc'],
            ['id', 'desc'],
        ])->values();

        // Pagination / Limit logic
        if (isset($filters['page']) || isset($filters['per_page'])) {
            $page = (int) ($filters['page'] ?? 1);
            $perPage = (int) ($filters['per_page'] ?? 10);
            
            $items = $sortedCandidates->slice(($page - 1) * $perPage, $perPage)->values();
            
            return new LengthAwarePaginator(
                $items,
                $sortedCandidates->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            $limit = (int) ($filters['limit'] ?? 8);
            return $sortedCandidates->take($limit)->all();
        }
    }

    private function scoreCandidates(Course $currentCourse, Collection $candidates): Collection
    {
        $currentCategoryIds = $currentCourse->categories->pluck('id')->toArray();

        foreach ($candidates as $candidate) {
            $score = 0;
            $reasons = [];

            // 1. Same category (+100)
            $candidateCategoryIds = $candidate->categories->pluck('id')->toArray();
            if (!empty(array_intersect($currentCategoryIds, $candidateCategoryIds))) {
                $score += 100;
                $reasons[] = 'Cùng danh mục';
            }

            // 2. Same level (+40)
            if ($currentCourse->level && $candidate->level === $currentCourse->level) {
                $score += 40;
                $reasons[] = 'Cùng cấp độ';
            }

            // 3. Same instructor (+25)
            if ($candidate->instructor_id === $currentCourse->instructor_id) {
                $score += 25;
                $reasons[] = 'Cùng giảng viên';
            }

            // 4. Featured (+10)
            if ($candidate->is_featured) {
                $score += 10;
            }

            // 5. Good rating (+0 to +20)
            if ($candidate->rating_avg !== null) {
                // E.g., rating_avg = 5 -> +20; rating_avg = 4 -> +16
                $ratingScore = (float) $candidate->rating_avg * 4;
                $score += $ratingScore;
                if ($candidate->rating_avg >= 4) {
                    $reasons[] = 'Đánh giá tốt';
                }
            }

            // Assign score and reasons to object properties dynamically
            $candidate->score = $score;
            $candidate->reasons = $reasons;
        }

        // Only return candidates that have some score > 0 (to actually be "related")
        // Or if requirement doesn't restrict, we return all sorted by score. The requirements:
        // "Tính động theo request ... Nếu không có khóa học liên quan -> 200, data: []"
        // Let's filter out ones with 0 score
        return $candidates->filter(function ($c) {
            return $c->score > 0;
        });
    }
}
