<?php
namespace App\Services\Learning;
use App\Exceptions\BusinessException;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
final class WatermarkInfoService
{
    public function getWatermarkInfo(int $learnerId, int $lessonId, array $filters): array
    {
        $lesson = Lesson::query()
            ->with('course')
            ->whereKey($lessonId)
            ->first();
        if (!$lesson || !$lesson->course) {
            throw new BusinessException('Không tìm thấy bài học.', 404);
        }
        $isOwnerOrAdmin = ((int) $lesson->course->instructor_id === $learnerId) || (User::query()->find($learnerId)?->role === 'admin');

        if (!$isOwnerOrAdmin) {
            if ((string) $lesson->status !== 'published' || (string) $lesson->course->status !== 'published') {
                throw new BusinessException('Nội dung chưa khả dụng.', 403);
            }
            if (!$lesson->is_preview && !$this->hasEnrollment($learnerId, (int) $lesson->course_id)) {
                throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
            }
        }
        $user = User::query()->find($learnerId);
        if (!$user) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }
        $mode = (string) ($filters['mode'] ?? 'moving');
        return [
            'text' => $this->maskEmail((string) $user->email) . ' - ID ' . $user->id,
            'mode' => $mode,
            'opacity' => 0.25,
            'refresh_seconds' => $mode === 'moving' ? 30 : 0,
            'position_rule' => $mode === 'moving' ? 'random' : 'bottom-right',
            'generated_at' => now()->toIso8601String(),
        ];
    }
    private function hasEnrollment(int $learnerId, int $courseId): bool
    {
        $query = DB::table('enrollments')
            ->where('user_id', $learnerId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed']);
        if (Schema::hasColumn('enrollments', 'deleted_at')) {
            $query;
        }
        $has = $query->exists();
        if (!$has) {
            $hasPaidOrder = DB::table('orders')
                ->where('user_id', $learnerId)
                ->where('course_id', $courseId)
                ->whereIn('status', ['paid', 'completed'])
                ->exists();
            if ($hasPaidOrder) {
                DB::table('enrollments')->insertOrIgnore([
                    'user_id' => $learnerId,
                    'course_id' => $courseId,
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return true;
            }
        }
        return $has;
    }
    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return 'user***';
        }
        [$local, $domain] = explode('@', $email, 2);
        $prefixLength = strlen($local) <= 3 ? 1 : 3;
        $prefix = substr($local, 0, $prefixLength);
        return $prefix . '***@' . $domain;
    }
}