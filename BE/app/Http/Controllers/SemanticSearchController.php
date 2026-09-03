<?php

namespace App\Http\Controllers;

use App\Services\Search\SearchAnalyticsService;
use App\Services\Search\SemanticSearchService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemanticSearchController extends Controller
{
    public function __construct(
        private readonly SemanticSearchService $semanticSearchService,
        private readonly SearchAnalyticsService $analyticsService
    ) {
    }

    /**
     * AI Vector Semantic Search for Courses
     */
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', $request->input('query', ''));
        $filters = [
            'category_id' => $request->input('category_id'),
            'course_level' => $request->input('course_level'),
            'min_score' => $request->input('min_score', 0.30),
            'limit' => $request->input('limit', 12),
            'user_id' => $request->user()?->id,
            'session_id' => (string) $request->header('X-Session-ID', $request->input('session_id', '')),
            'ip_address' => $request->ip(),
        ];

        $results = $this->semanticSearchService->search($query, $filters);

        return ApiResponse::success($results, 'Tìm kiếm ngữ nghĩa AI thành công');
    }

    /**
     * Get Top trending search terms
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = max(1, min(20, (int) $request->input('limit', 8)));
        $trending = $this->analyticsService->getTopTrendingSearches($limit);

        return ApiResponse::success($trending, 'Lấy danh sách tìm kiếm phổ biến thành công');
    }

    /**
     * Record a click event on search result
     */
    public function recordClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'search_log_id' => 'nullable|integer',
            'query' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:1',
            'time_to_click_seconds' => 'nullable|integer|min:0',
        ]);

        $click = $this->analyticsService->recordClick(
            $validated['search_log_id'] ?? null,
            (int) $validated['course_id'],
            $validated['query'] ?? null,
            (int) ($validated['position'] ?? 1),
            $validated['time_to_click_seconds'] ?? null
        );

        return ApiResponse::success(['recorded' => (bool) $click], 'Ghi nhận lượt nhấp thành công');
    }

    /**
     * Get AI similar courses by vector
     */
    public function similarCourses(int $id, Request $request): JsonResponse
    {
        $limit = max(1, min(12, (int) $request->input('limit', 4)));
        $similar = $this->semanticSearchService->getSimilarCourses($id, $limit);

        return ApiResponse::success($similar, 'Lấy danh sách khóa học tương tự thành công');
    }
}
