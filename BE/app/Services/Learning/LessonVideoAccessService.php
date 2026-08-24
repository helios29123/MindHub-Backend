<?php
namespace App\Services\Learning;
use App\Exceptions\BusinessException;
use App\Models\Lesson;
use App\Support\VideoStream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;
final class LessonVideoAccessService
{
    public function __construct(
        private readonly VideoStream $videoStream
    ) {
    }
    public function generateSignedStreamUrl(int $learnerId, int $lessonId, ?int $ttlSeconds, Request $request): array
    {
        $ttlSeconds = $ttlSeconds !== null
            ? (int) $ttlSeconds
            : (int) config('filesystems.video_stream_ttl_seconds', 600);
        if ($ttlSeconds < 60 || $ttlSeconds > 600) {
            throw new BusinessException('Thời hạn link xem video không hợp lệ.', 422);
        }
        $lesson = $this->getAccessibleVideoLesson($learnerId, $lessonId);
        
        if ($lesson->video_provider === 'bunny' && !empty($lesson->video_id)) {
            $hostname = config('bunny.stream.cdn_hostname');
            $streamUrl = "https://{$hostname}/{$lesson->video_id}/playlist.m3u8";
            $expiresAt = now()->addSeconds($ttlSeconds);
            return [
                'stream_url' => $streamUrl,
                'expires_in' => $ttlSeconds,
                'expires_at' => $expiresAt->toIso8601String(),
            ];
        }

        $this->resolveExistingPrivateVideoPath($lesson);
        $session = $request->attributes->get('auth_session');
        $sessionId = (int) ($session?->id ?? 0);
        $expiresAt = now()->addSeconds($ttlSeconds);
        $streamUrl = URL::temporarySignedRoute(
            'learn.lessons.stream',
            $expiresAt,
            [
                'id' => $lessonId,
                'u' => $learnerId,
                's' => $sessionId,
            ]
        );
        return [
            'stream_url' => $streamUrl,
            'expires_in' => $ttlSeconds,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }
    public function streamVideo(int $learnerId, int $lessonId, Request $request): StreamedResponse
    {
        if (!$request->hasValidSignature()) {
            throw new BusinessException('Link xem video không hợp lệ hoặc đã hết hạn.', 403);
        }
        $signedUserId = (int) $request->query('u', 0);
        if ($signedUserId !== $learnerId) {
            throw new BusinessException('Link xem video không hợp lệ hoặc đã hết hạn.', 403);
        }
        $signedSessionId = (int) $request->query('s', 0);
        $currentSession = $request->attributes->get('auth_session');
        $currentSessionId = (int) ($currentSession?->id ?? 0);
        if ($signedSessionId > 0 && $currentSessionId > 0 && $signedSessionId !== $currentSessionId) {
            throw new BusinessException('Link xem video không hợp lệ hoặc đã hết hạn.', 403);
        }
        $lesson = $this->getAccessibleVideoLesson($learnerId, $lessonId);
        $absolutePath = $this->resolveExistingPrivateVideoPath($lesson);
        return $this->videoStream->stream($absolutePath, $request);
    }
    private function getAccessibleVideoLesson(int $learnerId, int $lessonId): Lesson
    {
        $lesson = Lesson::query()
            ->with('course')
            ->whereKey($lessonId)
            ->first();
        if (!$lesson || !$lesson->course) {
            throw new BusinessException('Không tìm thấy bài học.', 404);
        }
        if ((string) $lesson->lesson_type !== 'video') {
            throw new BusinessException('Bài học này không phải video.', 422);
        }
        $isOwnerOrAdmin = ((int) $lesson->course->instructor_id === $learnerId) || (\App\Models\User::find($learnerId)?->role === 'admin');

        if (!$isOwnerOrAdmin) {
            if ((string) $lesson->status !== 'published' || (string) $lesson->course->status !== 'published') {
                throw new BusinessException('Nội dung chưa khả dụng.', 403);
            }
            if (!$lesson->is_preview && !$this->hasEnrollment($learnerId, (int) $lesson->course_id)) {
                throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
            }
        }
        if (trim((string) $lesson->video_url) === '' && trim((string) $lesson->video_id) === '') {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        return $lesson;
    }
    private function resolveExistingPrivateVideoPath(Lesson $lesson): string
    {
        $rawPath = (string) $lesson->video_url;
        if (trim($rawPath) === '') {
            throw new BusinessException('Không tìm thấy video.', 404);
        }

        if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($rawPath);
            $pathInUrl = ltrim($parsed['path'] ?? '', '/');
            if (str_starts_with($pathInUrl, 'storage/')) {
                $relativePath = substr($pathInUrl, strlen('storage/'));
                if (Storage::disk('public')->exists($relativePath)) {
                    return Storage::disk('public')->path($relativePath);
                }
            }
            return $rawPath;
        }

        $cleanPath = ltrim($rawPath, '/');
        $cleanPath = preg_replace('#^storage/#', '', $cleanPath);

        if (Storage::disk('public')->exists($cleanPath)) {
            return Storage::disk('public')->path($cleanPath);
        }

        $disk = (string) config('filesystems.video_disk', 'private_media');
        if (Storage::disk($disk)->exists($cleanPath)) {
            return Storage::disk($disk)->path($cleanPath);
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return public_path('storage/' . $cleanPath);
        }

        if (file_exists($cleanPath)) {
            return $cleanPath;
        }

        throw new BusinessException('Không tìm thấy video.', 404);
    }
}