<?php
namespace App\Http\Requests\Marketing;
use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CouponQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in([
                Coupon::STATUS_ACTIVE,
                Coupon::STATUS_INACTIVE,
                Coupon::STATUS_EXPIRED,
                Coupon::STATUS_USED_UP,
            ])],
            'code' => ['nullable', 'string', 'max:50'],
        ];
    }
    public function messages(): array
    {
        return [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang không hợp lệ.',
            'per_page.integer' => 'Số dòng mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số dòng mỗi trang không hợp lệ.',
            'per_page.max' => 'Số dòng mỗi trang tối đa là 100.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.min' => 'Khóa học không hợp lệ.',
            'status.in' => 'Trạng thái coupon không hợp lệ.',
            'code.string' => 'Mã coupon không hợp lệ.',
            'code.max' => 'Mã coupon không được vượt quá 50 ký tự.',
        ];
    }
}