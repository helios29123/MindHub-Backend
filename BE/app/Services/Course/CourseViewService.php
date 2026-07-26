<?php

namespace App\Services\Course;

use App\Models\Course;
use App\Models\CourseView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CourseViewService
{
    /**
     * Anti-duplicate window in minutes.
     */
    public const DUPLICATE_WINDOW_MINUTES = 30;

    /**
     * Record a course view if anti-duplicate rules pass.
     */
    public function recordView(Course $course, ?User $user = null, ?Request $request = null): bool
    {
        try {
            $request = $request ?? request();

            // 1. Exclude bot / crawler requests
            if ($this->isBot($request)) {
                return false;
            }

            // 2. Exclude instructor viewing their own course
            if ($user && (int) $user->id === (int) $course->instructor_id) {
                return false;
            }

            $ip = $request->ip();
            $userAgent = $request->header('User-Agent');
            $sessionId = $request->hasSession() ? $request->session()->getId() : $request->header('X-Session-ID');

            $ipHash = $ip ? hash('sha256', $ip) : null;
            $uaHash = $userAgent ? hash('sha256', $userAgent) : null;

            // 3. Anti-duplicate check (last 30 minutes)
            $since = Carbon::now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES);

            $query = CourseView::where('course_id', $course->id)
                ->where('viewed_at', '>=', $since);

            if ($user) {
                $query->where(function ($q) use ($user, $sessionId, $ipHash) {
                    $q->where('user_id', $user->id);
                    if ($sessionId) {
                        $q->orWhere('session_id', $sessionId);
                    }
                    if ($ipHash) {
                        $q->orWhere('ip_hash', $ipHash);
                    }
                });
            } elseif ($sessionId) {
                $query->where(function ($q) use ($sessionId, $ipHash) {
                    $q->where('session_id', $sessionId);
                    if ($ipHash) {
                        $q->orWhere('ip_hash', $ipHash);
                    }
                });
            } else {
                $query->where('ip_hash', $ipHash)
                    ->where('user_agent_hash', $uaHash);
            }

            if ($query->exists()) {
                return false; // Duplicate view within window
            }

            // 4. Create view record
            CourseView::create([
                'course_id' => $course->id,
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'ip_hash' => $ipHash,
                'user_agent_hash' => $uaHash,
                'viewed_at' => Carbon::now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            // View recording should never crash the main detail response
            Log::error('Failed to log course view: ' . $e->getMessage(), [
                'course_id' => $course->id,
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Determine if the request comes from a bot or crawler.
     */
    private function isBot(Request $request): bool
    {
        $userAgent = strtolower((string) $request->header('User-Agent'));

        if ($userAgent === '') {
            return false;
        }

        $botKeywords = [
            'bot', 'crawler', 'spider', 'slurp', 'bingbot', 'googlebot',
            'curl', 'wget', 'python-requests', 'postman', 'uptime', 'healthcheck'
        ];

        foreach ($botKeywords as $keyword) {
            if (str_contains($userAgent, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
