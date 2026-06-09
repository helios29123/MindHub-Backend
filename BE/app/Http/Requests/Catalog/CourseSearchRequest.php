<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CourseSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'level' => ['nullable', 'string', 'in:beginner,intermediate,advanced,all_levels'],
            'language' => ['nullable', 'string', 'max:20'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', 'string', 'in:latest,price_asc,price_desc,rating_desc,best_selling,featured'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Từ khóa tìm kiếm không hợp lệ.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',

            'category_id.integer' => 'Danh mục không hợp lệ.',
            'category_id.min' => 'Danh mục không hợp lệ.',

            'level.in' => 'Cấp độ khóa học không hợp lệ.',
            'language.max' => 'Ngôn ngữ không hợp lệ.',

            'min_price.numeric' => 'Giá thấp nhất phải là số.',
            'min_price.min' => 'Giá thấp nhất không được âm.',

            'max_price.numeric' => 'Giá cao nhất phải là số.',
            'max_price.min' => 'Giá cao nhất không được âm.',
            'max_price.gte' => 'Giá cao nhất phải lớn hơn hoặc bằng giá thấp nhất.',

            'sort.in' => 'Giá trị sắp xếp không hợp lệ.',

            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',

            'per_page.integer' => 'Số phần tử mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số phần tử mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số phần tử mỗi trang không được vượt quá 50.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Tham số không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
