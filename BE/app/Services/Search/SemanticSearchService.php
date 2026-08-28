<?php

namespace App\Services\Search;

use App\Models\Course;
use App\Models\CourseEmbedding;
use App\Services\Ai\EmbeddingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SemanticSearchService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly SearchAnalyticsService $analyticsService
    ) {
    }

    /**
     * Perform Semantic AI Vector Search on Courses
     *
     * @param string $queryText
     * @param array $filters (category_id, level, min_score, limit, user_id, session_id)
     * @return array
     */
    public function search(string $queryText, array $filters = []): array
    {
        $cleanQuery = trim($queryText);
        $limit = max(1, min(50, (int) ($filters['limit'] ?? 12)));
        $minScore = (float) ($filters['min_score'] ?? 0.35);

        if ($cleanQuery === '') {
            return [
                'query' => '',
                'total_matched' => 0,
                'items' => [],
            ];
        }

        // 1. Generate query vector
        $queryVector = $this->embeddingService->generateEmbedding($cleanQuery);

        // 2. Fetch active/published courses with existing embeddings
        $courseQuery = Course::query()
            ->with(['instructor', 'categories'])
            ->withCount(['enrollments', 'reviews'])
            ->where('status', 'published')
            ->whereExists(function ($subQuery) {
                $subQuery->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.id', 'courses.instructor_id')
                    ->where('users.status', 'active')
                    ->where(function ($uq) {
                        $uq->whereNull('users.locked')->orWhere('users.locked', 0);
                    });
            });

        if (!empty($filters['category_id'])) {
            $catId = (int) $filters['category_id'];
            $courseQuery->whereHas('categories', fn($q) => $q->where('categories.id', $catId));
        }

        if (!empty($filters['course_level'])) {
            $courseQuery->where('course_level', $filters['course_level']);
        }

        $courses = $courseQuery->get();

        if ($courses->isEmpty()) {
            $this->analyticsService->recordSearch(
                $cleanQuery,
                $filters['user_id'] ?? null,
                $filters['session_id'] ?? '',
                0,
                $filters['ip_address'] ?? null
            );

            return [
                'query' => $cleanQuery,
                'total_matched' => 0,
                'items' => [],
            ];
        }

        // 3. Load embeddings map
        $courseIds = $courses->pluck('id')->toArray();
        $embeddings = CourseEmbedding::whereIn('course_id', $courseIds)
            ->get()
            ->keyBy('course_id');

        $scoredCourses = [];

        foreach ($courses as $course) {
            $embeddingRecord = $embeddings->get($course->id);
            $courseVector = null;

            if ($embeddingRecord && is_array($embeddingRecord->vector) && count($embeddingRecord->vector) > 0) {
                $courseVector = $embeddingRecord->vector;
            } else {
                // Auto-generate vector on-the-fly and save if missing
                try {
                    $payloadInfo = $this->embeddingService->buildCoursePayload($course);
                    $courseVector = $this->embeddingService->generateEmbedding($payloadInfo['payload']);
                    
                    CourseEmbedding::updateOrCreate(
                        ['course_id' => $course->id],
                        [
                            'embedding_model' => 'text-embedding-004',
                            'dimensions' => count($courseVector),
                            'vector' => $courseVector,
                            'payload_hash' => $payloadInfo['hash'],
                            'content_summary' => $payloadInfo['summary'],
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning("Failed to auto-embed course {$course->id}: " . $e->getMessage());
                }
            }

            if (!$courseVector) {
                continue;
            }

            // Calculate Cosine Similarity (0.0 -> 1.0)
            $cosineSim = $this->embeddingService->cosineSimilarity($queryVector, $courseVector);

            // Exact keyword bonus
            $keywordBonus = 0.0;
            $lowQuery = mb_strtolower($cleanQuery, 'UTF-8');
            $lowTitle = mb_strtolower($course->title, 'UTF-8');
            $lowDesc = mb_strtolower($course->short_description ?: '', 'UTF-8');

            if (str_contains($lowTitle, $lowQuery)) {
                $keywordBonus += 0.15;
            } elseif (str_contains($lowDesc, $lowQuery)) {
                $keywordBonus += 0.08;
            }

            // Normalize Rating (0.0 -> 1.0)
            $rating = (float) ($course->average_rating ?? 4.5);
            $normRating = max(0.0, min(1.0, $rating / 5.0));

            // Popularity Factor (0.0 -> 1.0)
            $enrollments = (int) ($course->enrollments_count ?? 0);
            $popularity = min(1.0, log(1 + $enrollments) / log(1 + 1000));

            // Final Hybrid Score Formula
            $finalScore = ($cosineSim * 0.65) + ($keywordBonus * 0.10) + ($normRating * 0.15) + ($popularity * 0.10);
            $finalScore = (float) min(1.0, max(0.0, $finalScore));

            if ($finalScore >= $minScore || $cosineSim >= 0.30) {
                $scoredCourses[] = [
                    'course' => $course,
                    'similarity_score' => round($cosineSim, 4),
                    'hybrid_score' => round($finalScore, 4),
                    'match_percentage' => (int) round(min(99, max(50, $finalScore * 100))),
                    'ai_match_reason' => $this->generateMatchReason($course, $cleanQuery, $cosineSim),
                ];
            }
        }

        // Sort by hybrid_score descending
        usort($scoredCourses, fn($a, $b) => $b['hybrid_score'] <=> $a['hybrid_score']);

        $topItems = array_slice($scoredCourses, 0, $limit);

        // Record Search Log
        $this->analyticsService->recordSearch(
            $cleanQuery,
            $filters['user_id'] ?? null,
            $filters['session_id'] ?? '',
            count($topItems),
            $filters['ip_address'] ?? null
        );

        return [
            'query' => $cleanQuery,
            'total_matched' => count($topItems),
            'items' => array_map(function ($item, $rank) {
                /** @var Course $course */
                $course = $item['course'];
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug ?: (string) $course->id,
                    'short_description' => $course->short_description,
                    'price' => (float) $course->price,
                    'sale_price' => $course->sale_price !== null ? (float) $course->sale_price : null,
                    'thumbnail_url' => $course->thumbnail_url,
                    'average_rating' => (float) ($course->average_rating ?? 5.0),
                    'reviews_count' => (int) ($course->reviews_count ?? 0),
                    'enrollments_count' => (int) ($course->enrollments_count ?? 0),
                    'course_level' => $course->course_level ?: 'All Levels',
                    'instructor' => $course->instructor ? [
                        'id' => $course->instructor->id,
                        'full_name' => $course->instructor->full_name,
                        'avatar_url' => $course->instructor->avatar_url,
                    ] : null,
                    'categories' => $course->categories->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])->toArray(),
                    'similarity_score' => $item['similarity_score'],
                    'hybrid_score' => $item['hybrid_score'],
                    'match_percentage' => $item['match_percentage'],
                    'ai_match_reason' => $item['ai_match_reason'],
                    'rank_position' => $rank + 1,
                ];
            }, $topItems, array_keys($topItems)),
        ];
    }

    /**
     * Get similar courses based on vector cosine similarity
     *
     * @param int $courseId
     * @param int $limit
     * @return array
     */
    public function getSimilarCourses(int $courseId, int $limit = 4): array
    {
        $targetCourse = Course::find($courseId);
        if (!$targetCourse) {
            return [];
        }

        $targetEmbedding = CourseEmbedding::where('course_id', $courseId)->first();
        if (!$targetEmbedding || !is_array($targetEmbedding->vector)) {
            $payloadInfo = $this->embeddingService->buildCoursePayload($targetCourse);
            $vec = $this->embeddingService->generateEmbedding($payloadInfo['payload']);
            $targetEmbedding = CourseEmbedding::create([
                'course_id' => $courseId,
                'embedding_model' => 'text-embedding-004',
                'dimensions' => count($vec),
                'vector' => $vec,
                'payload_hash' => $payloadInfo['hash'],
                'content_summary' => $payloadInfo['summary'],
            ]);
        }

        $allOtherEmbeddings = CourseEmbedding::where('course_id', '!=', $courseId)
            ->with(['course.instructor', 'course.categories'])
            ->get();

        $similar = [];
        foreach ($allOtherEmbeddings as $other) {
            if (!$other->course || $other->course->status !== 'published') {
                continue;
            }

            $sim = $this->embeddingService->cosineSimilarity($targetEmbedding->vector, $other->vector);
            if ($sim > 0.30) {
                $similar[] = [
                    'course' => $other->course,
                    'similarity_score' => round($sim, 4),
                    'match_percentage' => (int) round(min(99, max(50, $sim * 100))),
                ];
            }
        }

        usort($similar, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return array_map(function ($item) {
            /** @var Course $c */
            $c = $item['course'];
            return [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug ?: (string) $c->id,
                'price' => (float) $c->price,
                'sale_price' => $c->sale_price !== null ? (float) $c->sale_price : null,
                'thumbnail_url' => $c->thumbnail_url,
                'average_rating' => (float) ($c->average_rating ?? 5.0),
                'reviews_count' => (int) ($c->reviews_count ?? 0),
                'course_level' => $c->course_level ?: 'All Levels',
                'instructor' => $c->instructor ? [
                    'id' => $c->instructor->id,
                    'full_name' => $c->instructor->full_name,
                    'avatar_url' => $c->instructor->avatar_url,
                ] : null,
                'similarity_score' => $item['similarity_score'],
                'match_percentage' => $item['match_percentage'],
            ];
        }, array_slice($similar, 0, $limit));
    }

    /**
     * Generate concise human-friendly AI reasoning for why course matches query
     */
    private function generateMatchReason(Course $course, string $query, float $score): string
    {
        $cats = $course->categories->pluck('name')->filter()->join(', ');
        if ($score >= 0.70) {
            return "Nội dung khóa học và giáo trình bài học trùng khớp cao với mục tiêu \"{$query}\"" . ($cats ? " thuộc mảng {$cats}." : ".");
        }
        return "Khóa học cung cấp kiến thức nền tảng và kỹ năng liên quan mật thiết đến chủ đề \"{$query}\".";
    }
}
