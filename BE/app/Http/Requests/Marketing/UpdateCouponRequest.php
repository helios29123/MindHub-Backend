<?php

namespace App\Http\Requests\Marketing;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
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
            'code' => ['prohibited'],

            'course_id' => ['sometimes', 'required', 'integer', 'exists:courses,id'],
            'campaign_type' => ['sometimes', Rule::in([Coupon::CAMPAIGN_DISCOUNT, Coupon::CAMPAIGN_TRIAL])],
            'discount_type' => ['sometimes', 'nullable', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'discount_value' => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'max_discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'usage_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in([Coupon::STATUS_INACTIVE])],
        ];
    }

    public function messages(): array
    {
        return [
            'code.prohibited' => 'Không được phép thay đổi mã campaign sau khi đã tạo.',
            'status.in' => 'Instructor chỉ được tắt campaign hiện tại.',
        ];
    }
}
