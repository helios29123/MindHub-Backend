<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

final class InstructorIntroductionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === 'instructor';
    }

    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'expertise' => ['prohibited'],
            'experience_years' => ['prohibited'],
            'level' => ['prohibited'],

            'bio' => ['present', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bio.present' => 'Trường giới thiệu là bắt buộc trong request.',
            'bio.string' => 'Giới thiệu phải là chuỗi.',
            'bio.max' => 'Giới thiệu không được vượt quá 5000 ký tự.',

            'user_id.prohibited' => 'Không được truyền user_id.',
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'expertise.prohibited' => 'Không được cập nhật chuyên môn từ API giới thiệu.',
            'experience_years.prohibited' => 'Không được cập nhật kinh nghiệm từ API giới thiệu.',
            'level.prohibited' => 'Không được cập nhật cấp độ từ API giới thiệu.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('bio') && is_string($this->input('bio'))) {
            $bio = trim($this->input('bio'));

            $this->merge([
                'bio' => $bio === '' ? null : $bio,
            ]);
        }
    }
}