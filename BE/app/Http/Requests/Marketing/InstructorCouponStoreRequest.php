<?php
namespace App\Http\Requests\Marketing;
use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class InstructorCouponStoreRequest extends FormRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'code' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('coupons', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => [
                'required',
                'numeric',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('discount_type') === Coupon::TYPE_PERCENT && (float) $value > 100) {
                        $fail('Giá trị phần trăm giảm không được vượt quá 100%.');
                    }
                    if ($this->input('discount_type') === Coupon::TYPE_FIXED && (float) $value > 10000000) {
                        $fail('Mức giảm giá cố định không được vượt quá 10,000,000đ.');
                    }
                },
            ],
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
    public function messages(): array
    {
        return [
            'user_id.prohibited' => 'Không được truyền user_id.',
            'instructor_id.prohibited' => 'Không được truyền instructor_id.',
            'used_count.prohibited' => 'Không được nhập hoặc sửa số lượt đã dùng.',
            'deleted_at.prohibited' => 'Không được truyền deleted_at.',
            'course_id.required' => 'Vui lòng chọn khóa học.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.string' => 'Mã giảm giá không hợp lệ.',
            'code.max' => 'Mã giảm giá không được vượt quá 100 ký tự.',
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'name.required' => 'Vui lòng nhập tên mã giảm giá.',
            'name.string' => 'Tên mã giảm giá không hợp lệ.',
            'name.max' => 'Tên mã giảm giá không được vượt quá 255 ký tự.',
            'description.string' => 'Mô tả mã giảm giá không hợp lệ.',
            'discount_type.required' => 'Vui lòng chọn loại giảm giá.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Vui lòng nhập giá trị giảm.',
            'discount_value.numeric' => 'Giá trị giảm phải là số.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'max_order_amount.numeric' => 'Mức giảm tối đa phải là số.',
            'max_order_amount.min' => 'Mức giảm tối đa không được âm.',
            'usage_limit.integer' => 'Giới hạn lượt dùng phải là số nguyên.',
            'usage_limit.min' => 'Giới hạn lượt dùng phải lớn hơn 0.',
            'start_at.date' => 'Thời gian bắt đầu không hợp lệ.',
            'end_at.date' => 'Thời gian kết thúc không hợp lệ.',
            'status.in' => 'Trạng thái mã giảm giá không hợp lệ.',
        ];
    }
}