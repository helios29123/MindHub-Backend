<?php

namespace App\Services\Learning;

use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\User;

class WatermarkInfoService
{
    public function getWatermarkInfo(int $learnerId, int $lessonId, array $filters): array
    {
        $lesson = Lesson::with('course')->find($lessonId);

        if (!$lesson) {
            throw new \App\Exceptions\BusinessException('Không tìm thấy bài học.', 404);
        }

        $course = $lesson->course;

        // Verify access via enrollment or preview
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
            
            // Check if user is instructor
            if ((int) $course->instructor_id === $learnerId) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            throw new \App\Exceptions\BusinessException('Bạn chưa có quyền truy cập bài học này.', 403);
        }

        $user = User::find($learnerId);
        
        // Mask email
        $emailParts = explode('@', $user->email);
        $maskedEmail = $emailParts[0];
        if (strlen($maskedEmail) > 3) {
            $maskedEmail = substr($maskedEmail, 0, 3) . '***';
        } else {
            $maskedEmail = $maskedEmail . '***';
        }
        if (isset($emailParts[1])) {
            $maskedEmail .= '@' . $emailParts[1];
        }

        $text = $maskedEmail . ' - ID ' . $user->id;
        $mode = $filters['mode'] ?? 'moving';

        return [
            'text' => $text,
            'mode' => $mode,
            'opacity' => 0.25,
            'refresh_seconds' => $mode === 'moving' ? 30 : 0,
            'position_rule' => $mode === 'moving' ? 'random' : 'corners',
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
