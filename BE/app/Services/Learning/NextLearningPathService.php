<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Models\Category;
use App\Repositories\Learning\LearningRecommendationRepository;
use Illuminate\Support\Collection;

class NextLearningPathService
{
    private array $levelProgression = [
        'beginner' => 'intermediate',
        'intermediate' => 'advanced',
        'advanced' => 'advanced', // Can suggest related advanced
        'all_levels' => 'intermediate', // Fallback for all_levels
    ];

    public function __construct(
        private readonly LearningRecommendationRepository $repository
    ) {
    }

    public function getNextLearningPath(int $learnerId, array $filters): array
    {
        $enrollments = $this->repository->getLearnerEnrollmentsWithCourse($learnerId);

        // If no enrollments -> empty result or fallback
        if ($enrollments->isEmpty()) {
            return [];
        }

        $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();

        // 1. Determine the most prominent/recent category
        $categoryCounts = [];
        $latestCategoryIds = [];
        
        foreach ($enrollments as $index => $enrollment) {
            $course = $enrollment->course;
            if (!$course) continue;
            
            foreach ($course->categories as $category) {
                if ($category->status !== 'active') continue;
                
                $catId = $category->id;
                $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                
                if ($index === 0) {
                    $latestCategoryIds[] = $catId;
                }
            }
        }

        if (empty($categoryCounts)) {
            return [];
        }

        // Target category: the one requested, OR the most frequent, OR the most recent
        $targetCategoryId = null;
        if (!empty($filters['category_id'])) {
            $targetCategoryId = (int) $filters['category_id'];
        } else {
            // Sort by frequency
            arsort($categoryCounts);
            $targetCategoryId = array_key_first($categoryCounts);
        }

        // 2. Determine highest level learned in this category (or generally)
        $highestLevelValue = 0;
        $highestLevelStr = 'beginner';
        $levelValues = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'all_levels' => 1,
        ];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (!$course || !$course->level) continue;
            
            // If we have a target category, we might want to only look at levels in that category
            // But getting highest overall is also fine. Let's look at courses in target category if possible.
            $hasCat = $course->categories->contains('id', $targetCategoryId);
            if ($hasCat) {
                $lvlVal = $levelValues[$course->level] ?? 1;
                if ($lvlVal >= $highestLevelValue) {
                    $highestLevelValue = $lvlVal;
                    $highestLevelStr = $course->level;
                }
            }
        }

        // 3. Map to next level
        $nextLevel = $this->levelProgression[$highestLevelStr] ?? 'intermediate';

        // 4. Query next courses
        $query = $this->repository->getCandidatePublishedCourses($enrolledCourseIds);

        // Filter by category
        $query->whereHas('categories', function ($q) use ($targetCategoryId) {
            $q->where('categories.id', $targetCategoryId);
        });

        // Try to find the exact next level first
        $candidates = $query->clone()->where('level', $nextLevel)->get();

        $pathReason = '';

        if ($candidates->isNotEmpty()) {
            if ($highestLevelStr === 'advanced') {
                $pathReason = 'Khóa học nâng cao hơn khóa bạn đã học';
            } else {
                $pathReason = 'Cấp độ tiếp theo trong cùng danh mục';
            }
        } else {
            // If no courses at exact next level, fallback to higher level or any level in the same category
            // except beginner if they are already advanced
            $fallbackCandidates = $query->clone()->where('level', '!=', 'beginner')->get();
            if ($fallbackCandidates->isNotEmpty()) {
                $candidates = $fallbackCandidates;
                $pathReason = 'Khóa học chuyên sâu trong cùng danh mục';
            }
        }

        // Add reason
        foreach ($candidates as $candidate) {
            $candidate->path_reason = $pathReason;
        }

        // Sort by id desc (newest)
        $candidates = $candidates->sortByDesc('id')->values();

        $limit = (int) ($filters['limit'] ?? 8);
        return $candidates->take($limit)->all();
    }
}
