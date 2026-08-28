<?php

namespace App\Http\Requests\Marketing;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InstructorCouponStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([Coupon::STATUS_INACTIVE])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Instructor chỉ được tắt campaign. Campaign đã kết thúc không được mở lại.',
        ];
    }
}
