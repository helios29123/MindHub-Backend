<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Resources\Instructor\InstructorCourseResource;
use App\Services\Instructor\InstructorCourseService;
use Illuminate\Http\JsonResponse;

final class InstructorCourseController extends Controller
{
    public function __construct(
        private readonly InstructorCourseService $instructorCourseService
    ) {
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->instructorCourseService->createCourse(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo khóa học thành công.',
            'data' => new InstructorCourseResource($course),
        ], 201);
    }
}