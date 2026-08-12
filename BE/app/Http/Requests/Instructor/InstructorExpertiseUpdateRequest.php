<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class InstructorExpertiseUpdateRequest extends FormRequest
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
            'bio' => ['prohibited'],

            'expertise' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience_years' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:80'],
            'level' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                !$this->has('expertise')
                && !$this->has('experience_years')
                && !$this->has('level')
            ) {
                $validator->errors()->add(
                    'payload',
                    'Cần ít nhất một trường chuyên môn để cập nhật.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'expertise.string' => 'Chuyên môn phải là chuỗi.',
            'expertise.max' => 'Chuyên môn không được vượt quá 255 ký tự.',

            'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
            'experience_years.min' => 'Số năm kinh nghiệm không được nhỏ hơn 0.',
            'experience_years.max' => 'Số năm kinh nghiệm không được vượt quá 80.',

            'level.string' => 'Cấp độ giảng viên phải là chuỗi.',
            'level.max' => 'Cấp độ giảng viên không được vượt quá 50 ký tự.',

            'user_id.prohibited' => 'Không được truyền user_id.',
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'bio.prohibited' => 'Không được cập nhật giới thiệu từ API chuyên môn.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['expertise', 'level'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $value = trim($this->input($field));
                $data[$field] = $value === '' ? null : $value;
            }
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}