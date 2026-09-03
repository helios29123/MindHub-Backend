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
            'course_level' => $this->course_level,
            'language' => $this->language,
            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_duration_seconds' => (int) ($this->total_duration_seconds ?: \Illuminate\Support\Facades\DB::table('lessons')
                ->where('course_id', $this->id)
                
                ->sum('video_duration_seconds')),
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
                
                ->count()),
            
            'summary' => [
                'section_count' => (int) ($this->sections_count ?? $this->sections()->count()),
                'lesson_count' => (int) ($this->lessons_count ?? \Illuminate\Support\Facades\DB::table('lessons')
                    ->where('course_id', $this->id)
                    
                    ->count()),
                'asset_count' => (int) \Illuminate\Support\Facades\DB::table('lesson_assets')
                    ->join('lessons', 'lessons.id', '=', 'lesson_assets.lesson_id')
                    ->where('lessons.course_id', $this->id)
                    
                    
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

            'category_name' => $this->relationLoaded('categories') ? $this->categories->first()?->name : $this->categories()->first()?->name,
            'category' => ($firstCat = $this->relationLoaded('categories') ? $this->categories->first() : $this->categories()->first()) ? [
                'id' => $firstCat->id,
                'name' => $firstCat->name,
                'slug' => $firstCat->slug,
            ] : null,

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
                                'content' => $lesson->content,
                                'assets' => $lesson->relationLoaded('assets') ? $lesson->assets->map(fn($a) => [
                                    'id' => $a->id,
                                    'file_name' => $a->file_name,
                                    'file_url' => $a->file_url,
                                    'file_type' => $a->file_type,
                                    'file_size' => $a->file_size,
                                ]) : [],
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

        // Check 1: Thông tin cơ bản (Tiêu đề, mô tả)
        $titleLen = mb_strlen(trim((string) $course->title));
        $descLen = mb_strlen(trim((string) $course->description));
        $shortDescLen = mb_strlen(trim((string) $course->short_description));
        $infoPassed = $titleLen >= 10 && $descLen >= 20;
        $checks[] = [
            'name' => 'Thông tin cơ bản',
            'message' => $infoPassed ? 'Đầy đủ tiêu đề, mô tả ngắn và mô tả chi tiết.' : 'Tiêu đề (tối thiểu 10 ký tự) hoặc mô tả quá ngắn.',
            'passed' => $infoPassed
        ];
        if (!$infoPassed) {
            $passed = false;
            $missingItems[] = 'Tiêu đề khóa học cần >= 10 ký tự, mô tả chi tiết >= 20 ký tự.';
        }

        // Check 2: Hình ảnh & Video giới thiệu (Thumbnail & Intro Video)
        $hasThumb = !empty(trim((string) $course->thumbnail_url));
        $hasIntroVideo = !empty(trim((string) $course->intro_video_url));
        $mediaPassed = $hasThumb && $hasIntroVideo;
        $checks[] = [
            'name' => 'Hình ảnh & Video giới thiệu',
            'message' => $mediaPassed
                ? 'Đã có ảnh bìa đại diện và video trailer giới thiệu khóa học.'
                : (!$hasThumb && !$hasIntroVideo
                    ? 'Chưa có ảnh bìa (thumbnail) và video trailer giới thiệu.'
                    : (!$hasThumb ? 'Chưa tải lên ảnh bìa (thumbnail).' : 'Chưa tải lên video trailer giới thiệu.')),
            'passed' => $mediaPassed
        ];
        if (!$hasThumb) {
            $passed = false;
            $missingItems[] = 'Bắt buộc tải lên ảnh bìa đại diện khóa học (Thumbnail).';
        }
        if (!$hasIntroVideo) {
            $passed = false;
            $missingItems[] = 'Bắt buộc tải lên video trailer giới thiệu khóa học (Intro Video).';
        }

        // Check 3: Giá bán hợp lệ theo sàn (>= 50.000 đ)
        $minPrice = (float) config('course.min_price', 50000);
        $price = (float) ($course->price ?? 0);
        $pricePassed = $price >= $minPrice;
        $checks[] = [
            'name' => 'Giá bán hợp lệ',
            'message' => $pricePassed ? 'Giá bán hợp lệ (' . number_format($price, 0, ',', '.') . 'đ).' : 'Giá bán phải đạt tối thiểu ' . number_format($minPrice, 0, ',', '.') . 'đ theo quy định sàn.',
            'passed' => $pricePassed
        ];
        if (!$pricePassed) {
            $passed = false;
            $missingItems[] = 'Giá bán phải tối thiểu ' . number_format($minPrice, 0, ',', '.') . 'đ theo quy định của sàn.';
        }

        // Check 4: Cấu trúc chương trình học (Chương & Bài học)
        $sectionsCount = $course->sections()->count();
        $lessons = \Illuminate\Support\Facades\DB::table('lessons')
            ->where('course_id', $course->id)
            ->get();
        $lessonsCount = $lessons->count();
        
        $curriculumPassed = $sectionsCount >= 1 && $lessonsCount >= 1;
        $checks[] = [
            'name' => 'Chương trình học',
            'message' => $curriculumPassed ? "Có {$sectionsCount} chương và {$lessonsCount} bài học." : 'Yêu cầu tối thiểu 1 chương và 1 bài học.',
            'passed' => $curriculumPassed
        ];
        if (!$curriculumPassed) {
            $passed = false;
            $missingItems[] = 'Khóa học phải có tối thiểu 1 chương học và 1 bài học.';
        }

        // Check 5: Nội dung từng bài học theo phân loại (Video / Tài liệu / Bài đọc)
        $invalidLessonsCount = 0;
        foreach ($lessons as $l) {
            $lType = strtolower((string) ($l->lesson_type ?? 'video'));
            if ($lType === 'video') {
                $vUrl = trim((string) ($l->video_url ?? ''));
                if ($vUrl === '' || str_starts_with($vUrl, 'blob:')) {
                    $invalidLessonsCount++;
                }
            } elseif ($lType === 'text') {
                if (trim((string) ($l->content ?? '')) === '') {
                    $invalidLessonsCount++;
                }
            } elseif ($lType === 'document') {
                $hasAsset = \Illuminate\Support\Facades\DB::table('lesson_assets')->where('lesson_id', $l->id)->exists();
                $hasContent = trim((string) ($l->content ?? '')) !== '';
                $hasUrl = trim((string) ($l->video_url ?? '')) !== '';
                if (!$hasAsset && !$hasContent && !$hasUrl) {
                    $invalidLessonsCount++;
                }
            }
        }
        $contentPassed = $lessonsCount > 0 && $invalidLessonsCount === 0;
        $checks[] = [
            'name' => 'Nội dung bài học',
            'message' => $contentPassed ? 'Tất cả bài học đều có nội dung/video/tài liệu hoàn chỉnh.' : ($invalidLessonsCount > 0 ? "Có {$invalidLessonsCount} bài học chưa hoàn thiện nội dung hoặc thiếu file/video." : 'Chưa có bài học để kiểm tra nội dung.'),
            'passed' => $contentPassed
        ];
        if ($invalidLessonsCount > 0) {
            $passed = false;
            $missingItems[] = "Có {$invalidLessonsCount} bài học chưa hoàn thiện nội dung (thiếu video, tài liệu hoặc nội dung bài viết).";
        }

        // Check 6: Học thử miễn phí (Preview)
        $previewLessonsCount = $lessons->where('is_preview', 1)->count();
        $previewPassed = $previewLessonsCount >= 1;
        $checks[] = [
            'name' => 'Học thử miễn phí (Preview)',
            'message' => $previewPassed ? "Có {$previewLessonsCount} bài học được bật chế độ học thử miễn phí." : 'Chưa có bài học nào được bật Học thử miễn phí (Preview).',
            'passed' => $previewPassed
        ];
        if (!$previewPassed) {
            $passed = false;
            $missingItems[] = 'Khóa học phải có ít nhất 1 bài học được bật "Học thử miễn phí (Preview)".';
        }

        $summary = $passed ? 'Khóa học đạt đủ điều kiện phê duyệt.' : 'Khóa học chưa đủ điều kiện phê duyệt, vui lòng kiểm tra lại các mục chưa đạt.';

        return [
            'passed' => $passed,
            'summary' => $summary,
            'missing_items' => $missingItems,
            'warnings' => $warnings,
            'checks' => $checks
        ];
    }
}
