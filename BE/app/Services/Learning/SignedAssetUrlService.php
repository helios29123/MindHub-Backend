<?php

namespace App\Services\Learning;

use App\Repositories\Learning\LessonAssetRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\Enrollment;

class SignedAssetUrlService
{
    public function __construct(
        private readonly LessonAssetRepository $repository
    ) {
    }

    public function generateSignedAssetUrl(int $learnerId, int $assetId, ?int $ttlSeconds): array
    {
        $asset = $this->repository->findById($assetId);

        if (!$asset || !$asset->lesson || !$asset->lesson->course) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy tài nguyên.', 404);
        }

        $lesson = $asset->lesson;
        $course = $lesson->course;

        // Check if lesson is previewable and published. If previewable, we might allow it without enrollment.
        // But the requirement says: "Learner có enrollment course chứa lesson". Let's enforce enrollment unless preview.
        $hasAccess = false;

        if ($lesson->is_preview && $lesson->status === 'published' && $course->status === 'published') {
            $hasAccess = true;
        } else {
            $enrollment = Enrollment::where('user_id', $learnerId)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment && in_array($enrollment->status, ['active', 'completed'])) {
                $hasAccess = true;
            }
            
            // Check if user is instructor of the course
            if ((int) $course->instructor_id === $learnerId) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
        }

        $ttlSeconds = $ttlSeconds ?? 300; // default 300s
        $expiresAt = now()->addSeconds($ttlSeconds);

        // Try to generate temporary URL
        try {
            // Assume the file path is stored in file_url relatively or fully.
            // If it's a full URL, we might need to extract the path.
            // If it's stored on S3/MinIO, Storage::disk('s3')->temporaryUrl() works.
            $disk = config('filesystems.default');
            $path = $asset->file_url;
            
            // Basic cleanup if the URL is absolute but stored on the disk
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($path);
                $path = ltrim($parsedUrl['path'], '/');
                // Remove bucket name from path if applicable, depends on setup
            }

            // Test if disk supports temporaryUrl
            if (method_exists(Storage::disk($disk)->getAdapter(), 'getTemporaryUrl') || config('filesystems.default') === 's3') {
                $url = Storage::disk($disk)->temporaryUrl($path, $expiresAt);
            } else {
                // If local disk doesn't support temporary URLs out of the box (without plugin)
                throw new \Exception('Disk does not support temporary URLs');
            }
        } catch (\Exception $e) {
            // Fallback if storage doesn't support signed URLs
            throw new \App\Exceptions\BusinessException('Hạ tầng lưu trữ chưa hỗ trợ URL tạm thời.', 503);
        }

        return [
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
            'ttl_seconds' => $ttlSeconds,
            'file_type' => $asset->file_type,
            'file_size' => $asset->file_size,
        ];
    }
}
