<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class NextLearningPathRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id,deleted_at,NULL'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Danh mục không hợp lệ hoặc không tồn tại.',
        ];
    }
}
