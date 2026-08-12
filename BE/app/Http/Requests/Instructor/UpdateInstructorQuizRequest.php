<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'lesson_id' => ['sometimes', 'integer', 'exists:lessons,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_score' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:draft,published,hidden'],

            'questions' => ['sometimes', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_type' => ['required', 'string', 'in:single_choice,multiple_choice,true_false'],
            'questions.*.score' => ['required', 'numeric', 'min:0'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.option_text' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_id.integer' => 'Bài học không hợp lệ.',
            'lesson_id.exists' => 'Bài học không tồn tại.',
            'title.string' => 'Tiêu đề quiz phải là chuỗi.',
            'title.max' => 'Tiêu đề quiz không được vượt quá 255 ký tự.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'passing_score.numeric' => 'Điểm đạt phải là số.',
            'passing_score.min' => 'Điểm đạt phải lớn hơn hoặc bằng 0.',
            'status.in' => 'Trạng thái quiz không hợp lệ.',
            'questions.array' => 'Danh sách câu hỏi không hợp lệ.',
            'questions.min' => 'Quiz phải có ít nhất một câu hỏi.',
            'questions.*.question_text.required' => 'Nội dung câu hỏi là bắt buộc.',
            'questions.*.question_type.required' => 'Loại câu hỏi là bắt buộc.',
            'questions.*.question_type.in' => 'Loại câu hỏi không hợp lệ.',
            'questions.*.score.required' => 'Điểm câu hỏi là bắt buộc.',
            'questions.*.score.numeric' => 'Điểm câu hỏi phải là số.',
            'questions.*.score.min' => 'Điểm câu hỏi phải lớn hơn hoặc bằng 0.',
            'questions.*.options.required' => 'Danh sách đáp án là bắt buộc.',
            'questions.*.options.array' => 'Danh sách đáp án không hợp lệ.',
            'questions.*.options.min' => 'Mỗi câu hỏi phải có ít nhất 2 đáp án.',
            'questions.*.options.*.option_text.required' => 'Nội dung đáp án là bắt buộc.',
            'questions.*.options.*.is_correct.required' => 'Trạng thái đáp án đúng/sai là bắt buộc.',
            'questions.*.options.*.is_correct.boolean' => 'Trạng thái đáp án đúng/sai phải là boolean.',
        ];
    }
}