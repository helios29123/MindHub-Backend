<?php

namespace App\Http\Controllers;

use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\CourseSectionResource;
use App\Http\Resources\Instructor\LessonResource;
use App\Http\Resources\Course\CourseReviewResource;
use App\Http\Resources\Course\InstructorResource;
use App\Http\Resources\Support\FaqResource;
use App\Services\Course\CoursePublicService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CoursePublicController extends Controller
{
    public function __construct(
        private readonly CoursePublicService $coursePublicService
    ) {
    }

    public function show(string $slug): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['slug' => $slug], [
            'slug' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->coursePublicService->show($slug);

        $resource = (new CourseResource($result['course']))->additional([
            'is_enrolled' => $result['is_enrolled'],
            'enrollment_status' => $result['enrollment_status'],
            'is_in_wishlist' => $result['is_in_wishlist'],
            'has_access' => $result['has_access'],
        ]);

        return ApiResponse::success($resource, 'Lấy chi tiết khóa học thành công');
    }

    public function outline(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->coursePublicService->outline((int) $id);

        $resource = CourseSectionResource::collection($result['sections']);
        $resource->collection->each(function ($secResource) use ($result) {
            $secResource->additional(['has_access' => $result['has_access']]);
        });

        return ApiResponse::success($resource, 'Lấy lộ trình khóa học thành công');
    }

    public function previewLesson(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $lesson = $this->coursePublicService->previewLesson((int) $id);

        $resource = new LessonResource($lesson);

        return ApiResponse::success($resource, 'Lấy bài học preview thành công');
    }

    public function reviews(mixed $id): JsonResponse
    {
        $input = array_merge(
            ['id' => $id],
            request()->only(['page', 'per_page', 'rating', 'sort'])
        );

        // Validate whitelist query parameters
        $allowedKeys = ['page', 'per_page', 'rating', 'sort'];
        $extraParams = array_diff(array_keys(request()->query()), $allowedKeys);

        if (!empty($extraParams)) {
            return ApiResponse::error('Tham số không hợp lệ.', ['query' => 'Chứa tham số không hợp lệ ngoài whitelist.'], 422);
        }

        $validator = Validator::make($input, [
            'id' => 'required|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort' => 'nullable|string|in:newest,highest_rating,lowest_rating',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->reviews((int) $id, $validator->validated());

        return ApiResponse::paginated(
            CourseReviewResource::collection($result['paginator']),
            $result['paginator'],
            'Lấy danh sách đánh giá thành công'
        );
    }

    public function showInstructor(mixed $id): JsonResponse
    {
        // Validate path parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->showInstructor((int) $id);

        $resource = new InstructorResource($result['instructor']);

        return ApiResponse::success($resource, 'Lấy thông tin giảng viên thành công');
    }

    public function faqs(mixed $id): JsonResponse
    {
        $input = array_merge(
            ['id' => $id],
            request()->only(['page', 'per_page'])
        );

        // Validate whitelist query parameters
        $allowedKeys = ['page', 'per_page'];
        $extraParams = array_diff(array_keys(request()->query()), $allowedKeys);

        if (!empty($extraParams)) {
            return ApiResponse::error('Tham số không hợp lệ.', ['query' => 'Chứa tham số không hợp lệ ngoài whitelist.'], 422);
        }

        $validator = Validator::make($input, [
            'id' => 'required|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Tham số không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $result = $this->coursePublicService->faqs((int) $id, $validator->validated());

        return ApiResponse::paginated(
            FaqResource::collection($result['paginator']),
            $result['paginator'],
            'Lấy danh sách FAQ thành công'
        );
    }
    public function relatedCourses(\App\Http\Requests\Course\RelatedCourseRequest $request, mixed $id, \App\Services\Course\RelatedCourseService $service): JsonResponse
    {
        // Path param courseId validates via regex in route, but we still ensure it's integer here
        $courseId = (int) $id;
        
        $filters = $request->validated();
        
        $result = $service->getRelatedCourses($courseId, $filters);
        
        if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            if ($result->isEmpty()) {
                return ApiResponse::success([], 'Không có khóa học liên quan.');
            }
            return ApiResponse::paginated(
                \App\Http\Resources\Course\RelatedCourseResource::collection($result),
                $result,
                'Lấy khóa học liên quan thành công.'
            );
        }
        
        if (empty($result)) {
            return ApiResponse::success([], 'Không có khóa học liên quan.');
        }
        
        return ApiResponse::success(
            \App\Http\Resources\Course\RelatedCourseResource::collection($result),
            'Lấy khóa học liên quan thành công.'
        );
    }

    public function recordView(mixed $id): JsonResponse
    {
        $course = \App\Models\Course::find($id);

        if (!$course) {
            return ApiResponse::error('Không tìm thấy khóa học.', [], 404);
        }

        $recorded = app(\App\Services\Course\CourseViewService::class)->recordView(
            $course,
            request()->user(),
            request()
        );

        return ApiResponse::success([
            'recorded' => $recorded,
        ], 'Ghi nhận lượt xem thành công.');
    }

    public function aiSearch(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'query' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $query = request()->input('query');

        // Fetch all active courses with relationships
        $courses = \App\Models\Course::where('status', 'published')->with(['instructor', 'categories'])->get();

        // Perform keyword and synonym ranking in PHP to keep prompt small and fast
        $q = mb_strtolower(trim($query), 'UTF-8');
        $words = array_filter(explode(' ', $q), function($w) {
            return mb_strlen($w, 'UTF-8') > 1;
        });

        $stopwords = [
            "khóa", "học", "khoá", "tìm", "kiếm", "muốn", "tôi", "cho", "về", "lớp", "hướng", "dẫn"
        ];
        $activeKeywords = array_filter($words, function($w) use ($stopwords) {
            return !in_array($w, $stopwords);
        });

        if (empty($activeKeywords)) {
            $activeKeywords = [$q];
        }

        $scored = [];
        foreach ($courses as $course) {
            $score = 0;
            $title = mb_strtolower($course->title, 'UTF-8');
            $desc = mb_strtolower($course->description, 'UTF-8');
            $shortDesc = mb_strtolower($course->short_description, 'UTF-8');
            $instructorName = mb_strtolower($course->instructor ? $course->instructor->name : '', 'UTF-8');

            if (str_contains($title, $q)) {
                $score += 15;
            }

            foreach ($activeKeywords as $kw) {
                if (str_contains($title, $kw)) $score += 5;
                if (str_contains($desc, $kw)) $score += 1;
                if (str_contains($shortDesc, $kw)) $score += 2;
                if (str_contains($instructorName, $kw)) $score += 3;
            }

            $synonyms = [
                "js" => ["javascript", "react", "next.js", "nodejs", "web", "frontend"],
                "javascript" => ["js", "react", "next.js", "nodejs", "web", "frontend"],
                "ai" => ["trí tuệ nhân tạo", "intelligence", "gemma", "chatgpt", "prompt", "machine learning"],
                "đồ họa" => ["figma", "thiết kế", "design", "ui/ux"],
                "thiết kế" => ["figma", "design", "ui/ux", "đồ họa"],
                "doanh nghiệp" => ["marketing", "kinh doanh", "branding"],
                "hacker" => ["bảo mật", "security", "hacking"],
                "web3" => ["solidity", "blockchain"],
                "facebook" => ["marketing", "growth hacking", "seo", "mạng xã hội", "quảng cáo", "ads"]
            ];

            foreach ($activeKeywords as $kw) {
                if (isset($synonyms[$kw])) {
                    foreach ($synonyms[$kw] as $syn) {
                        if (str_contains($title, $syn)) $score += 4;
                        if (str_contains($desc, $syn)) $score += 1;
                    }
                }
            }

            $scored[] = [
                'course' => $course,
                'score' => $score
            ];
        }

        usort($scored, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $relevantCourses = [];
        foreach ($scored as $item) {
            if ($item['score'] > 0) {
                $relevantCourses[] = $item['course'];
            }
        }

        $relevantCourses = array_slice($relevantCourses, 0, 6);
        if (empty($relevantCourses)) {
            $relevantCourses = $courses->slice(0, 6)->all();
        }

        // Format brief for prompt
        $coursesBriefList = [];
        foreach ($relevantCourses as $c) {
            $instructor = $c->instructor ? $c->instructor->name : 'N/A';
            $category = $c->categories->first() ? $c->categories->first()->name : 'N/A';
            $coursesBriefList[] = "- ID: course-{$c->id}\n  Tên: {$c->title}\n  Danh mục: {$category}\n  Mô tả: {$c->short_description}\n  Giảng viên: {$instructor}";
        }
        $coursesBrief = implode("\n", $coursesBriefList);

        // Load configuration and system prompt
        $aiConfig = config('ai');
        $apiKey = $aiConfig['api_key'];
        $baseUrl = $aiConfig['base_url'];
        $model = $aiConfig['model'];
        $systemPromptTemplate = $aiConfig['system_prompt'];

        // Replace parameters
        $promptContent = str_replace(
            ['{coursesBrief}', '{query}'],
            [$coursesBrief, $query],
            $systemPromptTemplate
        );

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ])->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $promptContent]
                ],
                'stream' => false
            ]);

            if ($response->failed()) {
                return ApiResponse::error('Lỗi khi kết nối với máy chủ AI: ' . $response->status(), [], 500);
            }

            $responseData = $response->json();
            $fullText = $responseData['choices'][0]['message']['content'] ?? '';

            // Clean up and extract recommended IDs
            $recommendedIds = [];
            if (preg_match('/RECOMMENDED_COURSES:\s*\[(.*?)\]/', $fullText, $match)) {
                $rawIds = explode(',', $match[1]);
                foreach ($rawIds as $rid) {
                    $cleanId = trim($rid);
                    if (str_starts_with($cleanId, 'course-')) {
                        $recommendedIds[] = $cleanId;
                    }
                }
            }

            $cleanText = preg_replace('/RECOMMENDED_COURSES:\s*\[.*?\]/i', '', $fullText);

            // Heuristic cleaning: keep only lines starting from the first Vietnamese-sounding text
            $lines = explode("\n", $cleanText);
            $vietnameseLines = [];
            $startedVietnamese = false;
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) {
                    if ($startedVietnamese) {
                        $vietnameseLines[] = '';
                    }
                    continue;
                }

                // Check for Vietnamese accent/diacritic characters to strip English target language parsing headers
                if (preg_match('/[àáảãạâầấẩẫậăằắẳẵặèéẻẽẹêềếểễệìíỉĩịòóỏõọôồốổỗộơờớởỡợùúủũụưừứửữựỳýỷỹỵđ]/iu', $trimmedLine)) {
                    $startedVietnamese = true;
                }

                if ($startedVietnamese) {
                    $vietnameseLines[] = $line;
                }
            }

            $finalText = trim(implode("\n", $vietnameseLines));
            if (empty($finalText)) {
                $finalText = trim($cleanText);
            }

            return ApiResponse::success([
                'text' => $finalText,
                'recommended_ids' => $recommendedIds
            ], 'Phản hồi từ AI nhận được thành công.');

        } catch (\Exception $e) {
            return ApiResponse::error('Đã xảy ra ngoại lệ: ' . $e->getMessage(), [], 500);
        }
    }

    public function aiPrompt(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'query' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $query = request()->input('query');

        // Fetch all active courses with relationships
        $courses = \App\Models\Course::where('status', 'published')->with(['instructor', 'categories'])->get();

        // Perform keyword and synonym ranking in PHP to keep prompt small and fast
        $q = mb_strtolower(trim($query), 'UTF-8');
        $words = array_filter(explode(' ', $q), function($w) {
            return mb_strlen($w, 'UTF-8') > 1;
        });

        $stopwords = [
            "khóa", "học", "khoá", "tìm", "kiếm", "muốn", "tôi", "cho", "về", "lớp", "hướng", "dẫn"
        ];
        $activeKeywords = array_filter($words, function($w) use ($stopwords) {
            return !in_array($w, $stopwords);
        });

        if (empty($activeKeywords)) {
            $activeKeywords = [$q];
        }

        $scored = [];
        foreach ($courses as $course) {
            $score = 0;
            $title = mb_strtolower($course->title, 'UTF-8');
            $desc = mb_strtolower($course->description, 'UTF-8');
            $shortDesc = mb_strtolower($course->short_description, 'UTF-8');
            $instructorName = mb_strtolower($course->instructor ? $course->instructor->name : '', 'UTF-8');

            if (str_contains($title, $q)) {
                $score += 15;
            }

            foreach ($activeKeywords as $kw) {
                if (str_contains($title, $kw)) $score += 5;
                if (str_contains($desc, $kw)) $score += 1;
                if (str_contains($shortDesc, $kw)) $score += 2;
                if (str_contains($instructorName, $kw)) $score += 3;
            }

            $synonyms = [
                "js" => ["javascript", "react", "next.js", "nodejs", "web", "frontend"],
                "javascript" => ["js", "react", "next.js", "nodejs", "web", "frontend"],
                "ai" => ["trí tuệ nhân tạo", "intelligence", "gemma", "chatgpt", "prompt", "machine learning"],
                "đồ họa" => ["figma", "thiết kế", "design", "ui/ux"],
                "thiết kế" => ["figma", "design", "ui/ux", "đồ họa"],
                "doanh nghiệp" => ["marketing", "kinh doanh", "branding"],
                "hacker" => ["bảo mật", "security", "hacking"],
                "web3" => ["solidity", "blockchain"],
                "facebook" => ["marketing", "growth hacking", "seo", "mạng xã hội", "quảng cáo", "ads"]
            ];

            foreach ($activeKeywords as $kw) {
                if (isset($synonyms[$kw])) {
                    foreach ($synonyms[$kw] as $syn) {
                        if (str_contains($title, $syn)) $score += 4;
                        if (str_contains($desc, $syn)) $score += 1;
                    }
                }
            }

            $scored[] = [
                'course' => $course,
                'score' => $score
            ];
        }

        usort($scored, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $relevantCourses = [];
        foreach ($scored as $item) {
            if ($item['score'] > 0) {
                $relevantCourses[] = $item['course'];
            }
        }

        $relevantCourses = array_slice($relevantCourses, 0, 6);
        if (empty($relevantCourses)) {
            $relevantCourses = $courses->slice(0, 6)->all();
        }

        // Format brief for prompt
        $coursesBriefList = [];
        foreach ($relevantCourses as $c) {
            $instructor = $c->instructor ? $c->instructor->name : 'N/A';
            $category = $c->categories->first() ? $c->categories->first()->name : 'N/A';
            $coursesBriefList[] = "- ID: course-{$c->id}\n  Tên: {$c->title}\n  Danh mục: {$category}\n  Mô tả: {$c->short_description}\n  Giảng viên: {$instructor}";
        }
        $coursesBrief = implode("\n", $coursesBriefList);

        $validIds = array_map(function($c) {
            return "course-{$c->id}";
        }, $relevantCourses);

        // Load configuration and system prompt templates
        $aiConfig = config('ai');
        $apiKey = $aiConfig['api_key'];
        $baseUrl = $aiConfig['base_url'];
        $model = $aiConfig['model'];
        
        $systemPromptTemplate = $aiConfig['system_prompt'];
        $userPromptTemplate = $aiConfig['user_prompt'] ?? 'Hãy tư vấn khóa học phù hợp cho tôi với chủ đề: {query}';

        // Replace parameters
        $systemPromptContent = str_replace('{coursesBrief}', $coursesBrief, $systemPromptTemplate);
        $userPromptContent = str_replace('{query}', $query, $userPromptTemplate);

        return ApiResponse::success([
            'system_prompt' => $systemPromptContent,
            'user_prompt' => $userPromptContent,
            'valid_ids' => $validIds,
            'api_key' => $apiKey,
            'base_url' => $baseUrl,
            'model' => $model
        ], 'Lấy prompt thành công.');
    }
}
