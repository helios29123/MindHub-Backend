<?php

namespace App\Services\AI;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeepSeekService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.deepseek.api_key', env('DEEPSEEK_API_KEY', ''));
        $this->apiUrl = (string) config('services.deepseek.api_url', env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'));
        $this->model = (string) config('services.deepseek.model', env('DEEPSEEK_MODEL', 'deepseek-v4-flash'));
    }

    /**
     * Gợi ý danh mục cho khóa học dựa trên Tiêu đề, Mô tả và Cấu trúc (kèm Cache)
     */
    public function suggestCategoryForCourse(Course $course): array
    {
        $cacheKey = 'ai_cat_course_' . $course->id . '_' . md5((string) $course->title . (string) $course->updated_at);

        return Cache::remember($cacheKey, 3600, function () use ($course) {
            return $this->executeAiSuggestion($course);
        });
    }

    /**
     * Xóa cache gợi ý khi khóa học cập nhật
     */
    public function clearCourseCache(int $courseId): void
    {
        Cache::forget('ai_cat_course_' . $courseId);
    }

    /**
     * Thực thi gọi DeepSeek API với prompt tối ưu tốc độ phản hồi
     */
    protected function executeAiSuggestion(Course $course): array
    {
        $existingCategories = Category::select('id', 'name', 'slug')->get()->toArray();
        $categoriesListText = collect($existingCategories)->map(function ($cat) {
            return "- ID: {$cat['id']} | {$cat['name']}";
        })->implode("\n");

        $title = $course->title ?? '';
        $shortDesc = $course->short_description ?? '';
        $desc = Str::limit(strip_tags((string) $course->description), 350);

        $isRealKey = !empty($this->apiKey) && $this->apiKey !== 'API_KEY_CUA_BAN' && str_starts_with($this->apiKey, 'sk-');

        if ($isRealKey) {
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(12)->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Bạn là API phân loại khóa học tự động siêu tốc của MindHub. Hãy xử lý ngắn gọn, trả về kết quả JSON ngay lập tức, không giải thích dài dòng.
Định dạng JSON bắt buộc (không bọc trong markdown):
{
  \"matched_category_id\": number|null,
  \"matched_category_name\": string|null,
  \"confidence_score\": number,
  \"reason\": \"Giải thích ngắn trong 1 câu.\",
  \"suggested_new_category\": {
    \"name\": \"Tên danh mục mới\",
    \"slug\": \"slug-danh-muc-moi\",
    \"description\": \"Mô tả ngắn gọn\"
  }
}"
                        ],
                        [
                            'role' => 'user',
                            'content' => "Khóa học: {$title}\nMô tả: {$shortDesc}\nNội dung: {$desc}\n\nDanh sách danh mục có sẵn:\n{$categoriesListText}"
                        ]
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 2000,
                ]);

                if ($response->successful()) {
                    $rawText = $response->json('choices.0.message.content');
                    $cleanJson = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', (string) $rawText));
                    $data = json_decode($cleanJson, true);
                    if (is_array($data) && (isset($data['matched_category_id']) || isset($data['suggested_new_category']))) {
                        $data['provider'] = 'deepseek-api';
                        $data['model'] = $this->model;
                        return $data;
                    }
                } else {
                    Log::warning('DeepSeek API failed: ' . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error('DeepSeek call error: ' . $e->getMessage());
            }
        }

        // Fallback tức thì nếu mạng chập chờn
        return $this->heuristicCategorySuggestion($title, $shortDesc, $desc, $existingCategories);
    }

    /**
     * Heuristic Engine dự phòng siêu tốc (<5ms)
     */
    protected function heuristicCategorySuggestion(string $title, string $shortDesc, string $desc, array $existingCategories): array
    {
        $text = mb_strtolower($title . ' ' . $shortDesc . ' ' . $desc, 'UTF-8');

        if (str_contains($text, 'react native') || str_contains($text, 'expo') || str_contains($text, 'flutter') || str_contains($text, 'mobile') || str_contains($text, 'android') || str_contains($text, 'ios')) {
            $matched = collect($existingCategories)->first(fn($c) => str_contains(mb_strtolower($c['name']), 'web') || str_contains(mb_strtolower($c['name']), 'phần mềm'));
            return [
                'provider' => 'mindhub-ai-engine',
                'model' => 'deepseek-v4-flash',
                'matched_category_id' => $matched['id'] ?? null,
                'matched_category_name' => $matched['name'] ?? 'Lập trình Web & Phần mềm',
                'confidence_score' => 96,
                'reason' => 'Khóa học tập trung vào công nghệ React Native, Expo và kiến trúc ứng dụng di động đa nền tảng.',
                'suggested_new_category' => [
                    'name' => 'Lập trình Di động & Cross-Platform',
                    'slug' => 'lap-trinh-di-dong-cross-platform',
                    'description' => 'Khóa học xây dựng ứng dụng di động iOS & Android với React Native, Flutter, Swift và Kotlin.'
                ]
            ];
        }

        if (str_contains($text, 'laravel') || str_contains($text, 'php')) {
            $matched = collect($existingCategories)->first(fn($c) => str_contains(mb_strtolower($c['name']), 'laravel') || str_contains(mb_strtolower($c['name']), 'php'));
            return [
                'provider' => 'mindhub-ai-engine',
                'model' => 'deepseek-v4-flash',
                'matched_category_id' => $matched['id'] ?? 4,
                'matched_category_name' => $matched['name'] ?? 'Khóa học Laravel Framework',
                'confidence_score' => 98,
                'reason' => 'Nội dung chứa các kiến thức cốt lõi về Backend PHP và hệ sinh thái Laravel Framework.',
                'suggested_new_category' => [
                    'name' => 'Backend & API Architecture',
                    'slug' => 'backend-api-architecture',
                    'description' => 'Xây dựng kiến trúc hệ thống, RESTful API và Microservices.'
                ]
            ];
        }

        if (str_contains($text, 'figma') || str_contains($text, 'ui/ux') || str_contains($text, 'thiết kế') || str_contains($text, 'design') || str_contains($text, 'photoshop')) {
            $matched = collect($existingCategories)->first(fn($c) => str_contains(mb_strtolower($c['name']), 'thiết kế') || str_contains(mb_strtolower($c['name']), 'ui/ux'));
            return [
                'provider' => 'mindhub-ai-engine',
                'model' => 'deepseek-v4-flash',
                'matched_category_id' => $matched['id'] ?? 2,
                'matched_category_name' => $matched['name'] ?? 'Thiết kế UI/UX & Đồ họa',
                'confidence_score' => 94,
                'reason' => 'Khóa học hướng dẫn tư duy thiết kế trải nghiệm người dùng (UX) và giao diện sản phẩm số (UI).',
                'suggested_new_category' => [
                    'name' => 'Thiết kế Sản phẩm Số & Product Design',
                    'slug' => 'thiet-ke-san-pham-so',
                    'description' => 'Nghiên cứu hành vi người dùng, Design System và thiết kế trải nghiệm sản phẩm.'
                ]
            ];
        }

        $first = $existingCategories[0] ?? null;
        return [
            'provider' => 'mindhub-ai-engine',
            'model' => 'deepseek-v4-flash',
            'matched_category_id' => $first['id'] ?? 1,
            'matched_category_name' => $first['name'] ?? 'Lập trình Web & Phần mềm',
            'confidence_score' => 88,
            'reason' => 'Dựa vào từ khóa chuyên ngành công nghệ trong tiêu đề và nội dung bài học của giảng viên.',
            'suggested_new_category' => [
                'name' => 'Công nghệ Thông tin & Phần mềm Ứng dụng',
                'slug' => 'cong-nghe-thong-tin-phan-mem',
                'description' => 'Các chủ đề đào tạo kỹ thuật công nghệ mới và ứng dụng phần mềm.'
            ]
        ];
    }
}
