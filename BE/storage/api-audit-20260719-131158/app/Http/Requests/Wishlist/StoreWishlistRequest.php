<?php
namespace App\Http\Requests\Wishlist;
use Illuminate\Foundation\Http\FormRequest;
final class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.required' => 'Vui lòng chọn khóa học.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.min' => 'Khóa học không hợp lệ.',
        ];
    }
}