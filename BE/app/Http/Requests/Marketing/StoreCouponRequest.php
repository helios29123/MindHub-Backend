<?php

namespace App\Http\Requests\Marketing;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'instructor_id' => ['prohibited'],
            'used_count' => ['prohibited'],
            'deleted_at' => ['prohibited'],
            'code' => ['required', 'string', 'max:80', 'unique:coupons,code', 'regex:/^[a-zA-Z0-9\-_]+$/'],

            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'campaign_type' => ['required', Rule::in([Coupon::CAMPAIGN_DISCOUNT, Coupon::CAMPAIGN_TRIAL])],
            'discount_type' => ['nullable', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'discount_value' => ['nullable', 'numeric', 'min:1'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],

        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã campaign.',
            'code.unique' => 'Mã campaign đã tồn tại, vui lòng chọn mã khác.',
            'code.regex' => 'Mã campaign không được chứa dấu cách hoặc ký tự đặc biệt.',
            'campaign_type.required' => 'Vui lòng chọn Giảm giá hoặc Học thử miễn phí.',
            'campaign_type.in' => 'Chế độ campaign không hợp lệ.',
            'course_id.required' => 'Vui lòng chọn khóa học.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'usage_limit.min' => 'Giới hạn lượt dùng phải từ 1 trở lên.',
            'end_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',

        ];
    }
}
