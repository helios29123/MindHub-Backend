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
            $libraryId = (string) config('bunny.stream.library_id', '724015');
            $hostname = (string) config('bunny.stream.cdn_hostname', 'vz-725f19ee-511.b-cdn.net');
            $embedUrl = "https://iframe.mediadelivery.net/embed/{$libraryId}/{$lesson->video_id}?autoplay=true&loop=false&muted=false&preload=true&responsive=true";
            $hlsUrl = "https://{$hostname}/{$lesson->video_id}/playlist.m3u8";
            $expiresAt = now()->addSeconds($ttlSeconds);
            return [
                'stream_url' => $embedUrl,
                'embed_url' => $embedUrl,
                'hls_url' => $hlsUrl,
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
        if ((string) $lesson->status !== 'published' || (string) $lesson->course->status !== 'published') {
            throw new BusinessException('Nội dung chưa khả dụng.', 403);
        }
        if (!$this->hasEnrollment($learnerId, (int) $lesson->course_id)) {
            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }
        if (trim((string) $lesson->video_url) === '') {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        return $lesson;
    }
    private function resolveExistingPrivateVideoPath(Lesson $lesson): string
    {
        $disk = (string) config('filesystems.video_disk', 'private_media');
        $path = ltrim((string) $lesson->video_url, '/');
        if (!$this->isPrivateRelativePath($path)) {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        if (!Storage::disk($disk)->exists($path)) {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        return Storage::disk($disk)->path($path);
    }
    private function hasEnrollment(int $learnerId, int $courseId): bool
    {
        return DB::table('enrollments')
            ->where('user_id', $learnerId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }
    private function isPrivateRelativePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return false;
        }
        return !str_starts_with($path, 'storage/')
            && !str_starts_with($path, '/storage/')
            && !str_starts_with($path, '/videos/');
    }
}