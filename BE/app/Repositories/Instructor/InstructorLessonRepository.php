<?php

namespace App\Repositories\Instructor;

use App\Models\Lesson;

final class InstructorLessonRepository
{
    public function findByIdWithCourse(int $lessonId): ?Lesson
    {
        return Lesson::query()
            ->with('course')
            ->whereKey($lessonId)
            ->first();
    }

    public function updateVideo(Lesson $lesson, string $videoUrl, ?int $videoDurationSeconds): Lesson
    {
        $lesson->update([
            'lesson_type' => 'video',
            'video_url' => $videoUrl,
            'video_duration_seconds' => $videoDurationSeconds ?? 0,
        ]);

        return $lesson->refresh();
    }
}