<?php

namespace App\Services\Search;

use App\Models\SearchClick;
use App\Models\SearchLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchAnalyticsService
{
    /**
     * Record a search event and update trending caches
     */
    public function recordSearch(
        string $query,
        ?int $userId,
        string $sessionId,
        int $resultsCount = 0,
        ?string $ipAddress = null
    ): ?SearchLog {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return null;
        }

        $normalizedQuery = mb_strtolower($cleanQuery, 'UTF-8');

        // Update In-Memory / Redis trending counter
        try {
            $todayKey = 'search_trending_' . date('Y_m_d');
            $trending = Cache::get($todayKey, []);
            $trending[$normalizedQuery] = ($trending[$normalizedQuery] ?? 0) + 1;
            // Keep top 100 queries
            arsort($trending);
            $trending = array_slice($trending, 0, 100, true);
            Cache::put($todayKey, $trending, now()->addDays(7));
        } catch (\Throwable $e) {
            Log::warning('Failed to update cache trending search: ' . $e->getMessage());
        }

        try {
            return SearchLog::create([
                'user_id' => $userId,
                'session_id' => $sessionId ?: md5($ipAddress . date('Ymd')),
                'query' => $cleanQuery,
                'normalized_query' => $normalizedQuery,
                'search_type' => 'semantic_vector',
                'results_count' => $resultsCount,
                'ip_address' => $ipAddress,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create search_log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Record a click event on a search result item
     */
    public function recordClick(
        ?int $searchLogId,
        int $courseId,
        ?string $query = null,
        int $position = 1,
        ?int $timeToClick = null
    ): ?SearchClick {
        try {
            // Update cache top course clicks
            $clickKey = 'search_top_clicked_courses_' . date('Y_m');
            $clicks = Cache::get($clickKey, []);
            $clicks[$courseId] = ($clicks[$courseId] ?? 0) + 1;
            arsort($clicks);
            $clicks = array_slice($clicks, 0, 100, true);
            Cache::put($clickKey, $clicks, now()->addDays(30));

            return SearchClick::create([
                'search_log_id' => $searchLogId,
                'course_id' => $courseId,
                'query' => $query,
                'clicked_position' => max(1, $position),
                'time_to_click_seconds' => $timeToClick,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create search_click: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Top trending search terms
     *
     * @param int $limit
     * @return array<string>
     */
    public function getTopTrendingSearches(int $limit = 8): array
    {
        $todayKey = 'search_trending_' . date('Y_m_d');
        $cached = Cache::get($todayKey);

        if (is_array($cached) && count($cached) > 0) {
            return array_slice(array_keys($cached), 0, $limit);
        }

        // Fallback to database queries in last 7 days
        try {
            $dbTop = DB::table('search_logs')
                ->select('normalized_query', DB::raw('COUNT(*) as search_count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('normalized_query')
                ->orderByDesc('search_count')
                ->limit($limit)
                ->pluck('normalized_query')
                ->toArray();

            if (!empty($dbTop)) {
                return $dbTop;
            }
        } catch (\Throwable $e) {
            /* ignore */
        }

        // Default smart curation fallback
        return [
            'Lập trình React 19',
            'Fullstack Web Developer',
            'Laravel Backend REST API',
            'Python AI & Machine Learning',
            'Thiết kế UI/UX Figma',
            'Docker & DevOps cho người mới',
        ];
    }
}
