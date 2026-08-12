<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class InstructorLearnerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'instructor';
    }

    protected function prepareForValidation(): void
    {
        $courseId = $this->input('courseId') ?? $this->input('course_id');
        $status = $this->input('status');
        $perPage = $this->input('per_page') ?? $this->input('limit');

        $mergeData = [];

        if ($courseId === 'all' || $courseId === '0' || $courseId === '') {
            $mergeData['course_id'] = null;
        } elseif ($courseId !== null && is_numeric($courseId)) {
            $mergeData['course_id'] = (int) $courseId;
        }

        if ($status === 'all' || $status === '') {
            $mergeData['status'] = null;
        }

        if ($perPage !== null && is_numeric($perPage)) {
            $mergeData['per_page'] = (int) $perPage;
        }

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
            'preset' => ['nullable', 'string'],
            'date_from' => ['nullable', 'string'],
            'date_to' => ['nullable', 'string'],
            'enrolled_from' => ['nullable', 'string'],
            'enrolled_to' => ['nullable', 'string'],
            'sort' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'minProgress' => ['nullable'],
            'maxProgress' => ['nullable'],
            'startDate' => ['nullable'],
            'endDate' => ['nullable'],
            'courseId' => ['nullable'],
        ];
    }
}