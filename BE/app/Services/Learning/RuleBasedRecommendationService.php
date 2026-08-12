<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Repositories\Learning\LearningRecommendationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RuleBasedRecommendationService
{
    // Define level progression mapping for "next closest level" score
    private array $levelProgression = [
        'beginner' => ['intermediate'],
        'intermediate' => ['advanced'],
        'advanced' => [],
        'all_levels' => [],
    ];

    public function __construct(
        private readonly LearningRecommendationRepository $repository
    ) {
    }

    public function getRuleBasedRecommendations(int $learnerId, array $filters): array|LengthAwarePaginator
    {
        $wishlistCourseIds = $this->repository->getLearnerWishlistCourseIds($learnerId);
        $enrolledCourseIds = $this->repository->getLearnerEnrolledCourseIds($learnerId);

        $interactedCourseIds = array_unique(array_merge($wishlistCourseIds, $enrolledCourseIds));

        $preferredCategoryIds = $this->repository->getPreferenceCategoryIds($interactedCourseIds);
        $preferredLevels = $this->repository->getCourseLevels($interactedCourseIds);

        // Target levels include preferred levels and their next logical steps
        $targetLevels = $this->calculateTargetLevels($preferredLevels);

        $excludeCourseIds = $enrolledCourseIds; // Only exclude enrolled/purchased, but maybe wishlist is okay? Requirement: "loại course learner đã enroll/mua"

        $query = $this->repository->getCandidatePublishedCourses($excludeCourseIds);
        $candidates = $query->get();

        $scoredCandidates = $this->scoreCandidates($candidates, $preferredCategoryIds, $targetLevels, $wishlistCourseIds);

        // Sort: score desc, id desc (fallback)
        $sortedCandidates = $scoredCandidates->sortBy([
            ['score', 'desc'],
            ['id', 'desc'],
        ])->values();

        // If learner has no history or all scores are 0, fallback to popular/featured
        if (empty($interactedCourseIds) || $sortedCandidates->isEmpty() || $sortedCandidates->first()->score == 0) {
            $sortedCandidates = $this->getFallbackRecommendations($candidates);
        } else {
            // Filter out 0 score candidates for personalized recommendations
            $sortedCandidates = $sortedCandidates->filter(fn($c) => $c->score > 0)->values();
        }

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

    private function calculateTargetLevels(array $preferredLevels): array
    {
        $targets = [];
        foreach ($preferredLevels as $level) {
            $targets[] = $level;
            if (isset($this->levelProgression[$level])) {
                $targets = array_merge($targets, $this->levelProgression[$level]);
            }
        }
        return array_unique($targets);
    }

    private function scoreCandidates(Collection $candidates, array $preferredCategoryIds, array $targetLevels, array $wishlistCourseIds): Collection
    {
        foreach ($candidates as $candidate) {
            $score = 0;
            $reasons = [];

            // 1. Same category (+70)
            $candidateCategoryIds = $candidate->categories->pluck('id')->toArray();
            if (!empty(array_intersect($preferredCategoryIds, $candidateCategoryIds))) {
                $score += 70;
                $reasons[] = 'Phù hợp danh mục bạn quan tâm';
            }

            // 2. Same level or next (+25)
            if ($candidate->level && in_array($candidate->level, $targetLevels)) {
                $score += 25;
                $reasons[] = 'Phù hợp cấp độ học của bạn';
            }

            // 3. Featured (+10)
            if ($candidate->is_featured) {
                $score += 10;
                $reasons[] = 'Khóa học nổi bật';
            }

            // 4. Good sale price (+5)
            if ($candidate->sale_price !== null && $candidate->sale_price < $candidate->price) {
                $score += 5;
            }

            // 5. Learner wishlisted this category/course (+20)
            // The requirement says: "course learner đã wishlist category: +20".
            // Since we already give +70 for category match, let's also give +20 if this specific course is in wishlist.
            if (in_array($candidate->id, $wishlistCourseIds)) {
                $score += 20;
            }

            $candidate->score = $score;
            $candidate->reasons = array_unique($reasons);
        }

        return $candidates;
    }

    private function getFallbackRecommendations(Collection $candidates): Collection
    {
        // Fallback: prioritize featured, then normal, give them a base score
        foreach ($candidates as $candidate) {
            $score = 0;
            $reasons = [];

            if ($candidate->is_featured) {
                $score += 50;
                $reasons[] = 'Khóa học nổi bật';
            } else {
                $score += 10;
                $reasons[] = 'Khóa học phổ biến';
            }

            $candidate->score = $score;
            $candidate->reasons = $reasons;
        }

        return $candidates->sortBy([
            ['score', 'desc'],
            ['id', 'desc'],
        ])->values();
    }
}
