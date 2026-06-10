<?php

namespace App\Services\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Repositories\Instructor\InstructorCourseRepository;
use App\Repositories\Instructor\InstructorLessonRepository;
use App\Support\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class InstructorCourseService
{
    public function __construct(
        private readonly InstructorCourseRepository $instructorCourseRepository,
        private readonly InstructorLessonRepository $instructorLessonRepository,
        private readonly FileUpload $fileUpload
    ) {
    }

    public function createCourse(User $instructor, array $validatedData): Course
    {
        return DB::transaction(function () use ($instructor, $validatedData): Course {
            $categoryIds = $validatedData['category_ids'] ?? [];

            unset($validatedData['category_ids']);

            $courseData = array_merge($validatedData, [
                'instructor_id' => $instructor->id,
                'status' => 'draft',
                'is_featured' => false,
                'total_duration_seconds' => 0,
                'published_at' => null,
                'admin_reject_reason' => null,
                'language' => $validatedData['language'] ?? 'vi',
                'level' => $validatedData['level'] ?? 'beginner',
            ]);

            $course = $this->instructorCourseRepository->create($courseData);

            if (!empty($categoryIds)) {
                $this->instructorCourseRepository->syncCategories($course, $categoryIds);
            }

            return $this->instructorCourseRepository->findWithCategories($course->id);
        });
    }

    public function uploadLessonVideo(User $instructor, int $lessonId, array $validatedData, UploadedFile $video): Lesson
    {
        return DB::transaction(function () use ($instructor, $lessonId, $validatedData, $video): Lesson {
            $lesson = $this->instructorLessonRepository->findByIdWithCourse($lessonId);

            if (!$lesson) {
                throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
            }

            if ((int) $lesson->course->instructor_id !== (int) $instructor->id) {
                throw new AccessDeniedHttpException('Bạn không có quyền thao tác tài nguyên này.');
            }

            $videoUrl = $this->fileUpload->uploadLessonVideo($video, $lesson->id);

            return $this->instructorLessonRepository->updateVideo(
                $lesson,
                $videoUrl,
                $validatedData['video_duration_seconds'] ?? null
            );
        });
    }
}