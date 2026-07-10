<?php
namespace App\Http\Requests\Marketing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
final class InstructorCouponStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,inactive'],
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = ['status'];
            $extraKeys = array_diff(array_keys($this->all()), $allowed);
            foreach ($extraKeys as $key) {
                $validator->errors()->add($key, 'API này chỉ cho phép cập nhật trạng thái.');
            }
        });
    }
    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái mã giảm giá.',
            'status.in' => 'Trạng thái mã giảm giá không hợp lệ.',
        ];
    }
}