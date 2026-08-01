<?php

namespace App\Http\Controllers;

use App\Services\Faq\FaqAdminService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminFaqController extends Controller
{
    protected FaqAdminService $faqService;

    public function __construct(FaqAdminService $faqService)
    {
        $this->faqService = $faqService;
    }

    /**
     * GET /api/admin/faqs
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->faqService->getFaqs($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách FAQ thành công.',
            'data' => [
                'summary' => $result['summary'],
                'items' => $result['items'],
            ],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * GET /api/admin/faqs/{id}
     */
    public function show(mixed $id): JsonResponse
    {
        $faq = $this->faqService->getFaqDetail((int) $id);
        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ không tồn tại hoặc đã bị xóa.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết FAQ thành công.',
            'data' => $faq,
        ]);
    }

    /**
     * POST /api/admin/faqs
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'type' => ['required', 'string', 'in:general,account,course,payment,refund,certificate,technical,policy'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ], [
            'question.required' => 'Câu hỏi không được để trống.',
            'answer.required' => 'Câu trả lời không được để trống.',
            'type.required' => 'Loại FAQ không được để trống.',
            'type.in' => 'Loại FAQ không hợp lệ.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học liên kết không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $faq = $this->faqService->createFaq($data);

        return response()->json([
            'success' => true,
            'message' => 'Tạo FAQ mới thành công.',
            'data' => $faq,
        ], 201);
    }

    /**
     * PATCH /api/admin/faqs/{id}
     */
    public function update(Request $request, mixed $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:general,account,course,payment,refund,certificate,technical,policy'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ], [
            'type.in' => 'Loại FAQ không hợp lệ.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'sort_order.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học liên kết không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $faq = $this->faqService->updateFaq((int) $id, $data);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ không tồn tại hoặc đã bị xóa.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật FAQ thành công.',
            'data' => $faq,
        ]);
    }

    /**
     * DELETE /api/admin/faqs/{id}
     */
    public function destroy(mixed $id): JsonResponse
    {
        $success = $this->faqService->deleteFaq((int) $id);
        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ không tồn tại hoặc đã bị xóa từ trước.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa FAQ thành công.',
        ]);
    }

    /**
     * PATCH /api/admin/faqs/{id}/courses
     */
    public function syncCourses(Request $request, mixed $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_ids' => ['required', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ], [
            'course_ids.required' => 'Danh sách khóa học không được để trống.',
            'course_ids.array' => 'Danh sách khóa học phải là một mảng.',
            'course_ids.*.exists' => 'Khóa học liên kết không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $courseIds = $request->input('course_ids', []);
        $faq = $this->faqService->syncFaqCourses((int) $id, $courseIds);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ không tồn tại hoặc đã bị xóa.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật liên kết khóa học thành công.',
            'data' => $faq,
        ]);
    }
}
