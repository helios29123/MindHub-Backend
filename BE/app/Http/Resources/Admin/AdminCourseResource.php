<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'instructor_id' => $this->instructor_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'level' => $this->level,
            'language' => $this->language,
            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_duration_seconds' => $this->total_duration_seconds,
            'published_at' => $this->published_at?->toIsoString(),
            'admin_reject_reason' => $this->admin_reject_reason,
            'created_at' => $this->created_at?->toIsoString(),
            'updated_at' => $this->updated_at?->toIsoString(),

            // Dynamic aggregates for Admin Course Management report
            'enrollment_count' => (int) ($this->enrollments_count ?? $this->enrollments()->count()),
            'paid_order_count' => (int) ($this->paid_order_count ?? $this->orders()->where('status', 'paid')->count()),
            'gross_revenue' => (float) ($this->gross_revenue ?? $this->orders()->where('status', 'paid')->sum('amount')),
            'average_rating' => (float) (isset($this->average_rating) ? round((float)$this->average_rating, 1) : ($this->reviews()->count() > 0 ? round($this->reviews()->avg('rating'), 1) : 0.0)),
            'review_count' => (int) ($this->review_count ?? $this->reviews()->count()),
            'comment_count' => (int) ($this->comment_count ?? \Illuminate\Support\Facades\DB::table('comments')
                ->join('lessons', 'lessons.id', '=', 'comments.lesson_id')
                ->where('lessons.course_id', $this->id)
                ->where('comments.status', 'visible')
                ->whereNull('lessons.deleted_at')
                ->count()),
            
            'summary' => [
                'section_count' => (int) ($this->sections_count ?? $this->sections()->count()),
                'lesson_count' => (int) ($this->lessons_count ?? \Illuminate\Support\Facades\DB::table('lessons')
                    ->where('course_id', $this->id)
                    ->whereNull('deleted_at')
                    ->count()),
                'asset_count' => (int) \Illuminate\Support\Facades\DB::table('lesson_assets')
                    ->join('lessons', 'lessons.id', '=', 'lesson_assets.lesson_id')
                    ->where('lessons.course_id', $this->id)
                    ->whereNull('lessons.deleted_at')
                    ->whereNull('lesson_assets.deleted_at')
                    ->count(),
            ],

            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor->id,
                    'full_name' => $this->instructor->full_name,
                    'email' => $this->instructor->email,
                    'phone' => $this->instructor->phone,
                    'role' => $this->instructor->role,
                    'status' => $this->instructor->status,
                ];
            }),

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'status' => $category->status,
                    ];
                });
            }),

            'sections' => $this->whenLoaded('sections', function () {
                return $this->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'course_id' => $section->course_id,
                        'title' => $section->title,
                        'position' => $section->position,
                        'status' => $section->status,
                        'created_at' => $section->created_at?->toIsoString(),
                        'updated_at' => $section->updated_at?->toIsoString(),
                    ];
                });
            }),

            'lessons' => $this->when(
                $this->relationLoaded('sections') && $this->sections->every(fn($s) => $s->relationLoaded('lessons')),
                function () {
                    $lessons = [];
                    foreach ($this->sections as $section) {
                        foreach ($section->lessons as $lesson) {
                            $lessons[] = [
                                'id' => $lesson->id,
                                'section_id' => $lesson->course_section_id,
                                'course_id' => $this->id,
                                'title' => $lesson->title,
                                'slug' => $lesson->slug,
                                'type' => $lesson->lesson_type ?? 'video',
                                'video_url' => $lesson->video_url,
                                'is_preview' => (bool) $lesson->is_preview,
                                'position' => $lesson->sort_order,
                                'total_duration_seconds' => $lesson->video_duration_seconds,
                                'status' => $lesson->status,
                            ];
                        }
                    }
                    return $lessons;
                }
            ),

            'checklist' => $this->when(
                $this->relationLoaded('sections') && $this->sections->every(fn($s) => $s->relationLoaded('lessons')),
                function () {
                    return $this->calculateChecklist($this->resource);
                }
            ),
        ];
    }

    private function calculateChecklist(\App\Models\Course $course): array
    {
        $checks = [];
        $missingItems = [];
        $warnings = [];
        $passed = true;

        // Check 1: Course Info
        $titleLen = mb_strlen($course->title);
        $descLen = mb_strlen($course->description);
        $infoPassed = $titleLen >= 10 && $descLen >= 20;
        $checks[] = [
            'name' => 'Thông tin cơ bản',
            'message' => $infoPassed ? 'Đầy đủ tiêu đề và mô tả chi tiết.' : 'Thiếu tiêu đề hoặc mô tả quá ngắn.',
            'passed' => $infoPassed
        ];
        if (!$infoPassed) {
            $passed = false;
            $missingItems[] = 'Tiêu đề phải >= 10 ký tự, mô tả >= 20 ký tự.';
        }

        // Check 2: Price
        $pricePassed = $course->price > 0;
        $checks[] = [
            'name' => 'Giá bán hợp lệ',
            'message' => $pricePassed ? 'Giá bán hợp lệ.' : 'Giá bán chưa được thiết lập.',
            'passed' => $pricePassed
        ];
        if (!$pricePassed) {
            $passed = false;
            $missingItems[] = 'Giá bán phải lớn hơn 0đ.';
        }

        // Check 3: Curriculum (Sections and lessons)
        $sectionsCount = $course->sections()->count();
        $lessonsCount = \Illuminate\Support\Facades\DB::table('lessons')
            ->where('course_id', $course->id)
            ->whereNull('deleted_at')
            ->count();
        
        $curriculumPassed = $sectionsCount >= 1 && $lessonsCount >= 1;
        $checks[] = [
            'name' => 'Chương trình học',
            'message' => $curriculumPassed ? "Có {$sectionsCount} chương và {$lessonsCount} bài học." : 'Chưa có chương hoặc bài học.',
            'passed' => $curriculumPassed
        ];
        if (!$curriculumPassed) {
            $passed = false;
            $missingItems[] = 'Yêu cầu có tối thiểu 1 chương và 1 bài học.';
        }

        // Check 4: Videos
        $lessonsWithoutVideo = \Illuminate\Support\Facades\DB::table('lessons')
            ->where('course_id', $course->id)
            ->whereNull('video_url')
            ->whereNull('deleted_at')
            ->count();
        
        $videoPassed = $lessonsCount > 0 && $lessonsWithoutVideo === 0;
        $checks[] = [
            'name' => 'Video bài giảng',
            'message' => $videoPassed ? 'Tất cả bài học đều có video.' : ($lessonsWithoutVideo > 0 ? "Có {$lessonsWithoutVideo} bài học chưa có video." : 'Không có bài học để kiểm tra video.'),
            'passed' => $videoPassed
        ];
        if ($lessonsWithoutVideo > 0) {
            $warnings[] = "Có {$lessonsWithoutVideo} bài học chưa upload video bài giảng.";
        }

        $summary = $passed ? 'Khóa học đạt đủ điều kiện phê duyệt.' : 'Khóa học chưa đủ điều kiện phê duyệt, vui lòng kiểm tra lại.';

        return [
            'passed' => $passed,
            'summary' => $summary,
            'missing_items' => $missingItems,
            'warnings' => $warnings,
            'checks' => $checks
        ];
    }
}
