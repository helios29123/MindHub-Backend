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
            'course_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in([
                Coupon::TYPE_PERCENT,
                Coupon::TYPE_FIXED,
            ])],
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('discount_type') === Coupon::TYPE_PERCENT && (float) $value > 100) {
                        $fail('Giảm giá phần trăm không được vượt quá 100.');
                    }
                },
            ],
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'status' => ['nullable', Rule::in([
                Coupon::STATUS_ACTIVE,
                Coupon::STATUS_INACTIVE,
                Coupon::STATUS_EXPIRED,
                Coupon::STATUS_USED_UP,
            ])],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.required' => 'Vui lòng chọn khóa học.',
            'course_id.integer' => 'Khóa học không hợp lệ.',
            'course_id.min' => 'Khóa học không hợp lệ.',
            'code.required' => 'Vui lòng nhập mã coupon.',
            'code.string' => 'Mã coupon không hợp lệ.',
            'code.max' => 'Mã coupon không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã coupon đã tồn tại.',
            'name.required' => 'Vui lòng nhập tên coupon.',
            'name.string' => 'Tên coupon không hợp lệ.',
            'name.max' => 'Tên coupon không được vượt quá 255 ký tự.',
            'description.string' => 'Mô tả coupon không hợp lệ.',
            'discount_type.required' => 'Vui lòng chọn loại giảm giá.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Vui lòng nhập giá trị giảm.',
            'discount_value.numeric' => 'Giá trị giảm phải là số.',
            'discount_value.min' => 'Giá trị giảm không được âm.',
            'max_order_amount.numeric' => 'Mức giảm tối đa phải là số.',
            'max_order_amount.min' => 'Mức giảm tối đa không được âm.',
            'usage_limit.integer' => 'Giới hạn lượt dùng phải là số nguyên.',
            'usage_limit.min' => 'Giới hạn lượt dùng không được âm.',
            'start_at.date' => 'Thời gian bắt đầu không hợp lệ.',
            'end_at.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'status.in' => 'Trạng thái coupon không hợp lệ.',
        ];
    }
}