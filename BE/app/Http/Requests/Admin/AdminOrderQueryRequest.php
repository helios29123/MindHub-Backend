<?php
namespace App\Http\Requests\Admin;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class AdminOrderQueryRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['pending', 'paid', 'cancelled', 'failed', 'expired'])],
            'payment_status' => ['nullable', 'string', Rule::in(['unpaid', 'processing', 'paid', 'failed'])],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            'course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->whereNull('deleted_at'),
            ],
            'order_code' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
    public function messages(): array
    {
        return [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'per_page.integer' => 'Số dòng mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số dòng mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số dòng mỗi trang không được vượt quá 100.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
            'user_id.integer' => 'Mã người dùng phải là số nguyên.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'course_id.integer' => 'Mã khóa học phải là số nguyên.',
            'course_id.exists' => 'Khóa học không tồn tại.',
            'order_code.string' => 'Mã đơn hàng phải là chuỗi.',
            'order_code.max' => 'Mã đơn hàng không được vượt quá 50 ký tự.',
            'date_from.date' => 'Ngày bắt đầu không hợp lệ.',
            'date_to.date' => 'Ngày kết thúc không hợp lệ.',
            'date_to.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Bộ lọc không hợp lệ.', $validator->errors()->toArray(), 422)
        );
    }
}