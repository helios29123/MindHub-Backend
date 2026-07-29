<?php
namespace App\Http\Requests\Moderation;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class RejectcourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $reason = $this->input('admin_reject_reason');
        if ($reason === null) {
            $reason = $this->input('reason');
        }
        $this->merge([
            'id' => $this->route('id'),
            'admin_reject_reason' => $reason,
        ]);
    }
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'admin_reject_reason' => ['bail', 'required', 'string', 'max:1000'],
        ];
    }
    public function messages(): array
    {
        return [
            'id.required' => 'Dữ liệu không hợp lệ.',
            'id.integer' => 'Dữ liệu không hợp lệ.',
            'id.min' => 'Dữ liệu không hợp lệ.',
            'admin_reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
            'admin_reject_reason.string' => 'Dữ liệu không hợp lệ.',
            'admin_reject_reason.max' => 'Dữ liệu không hợp lệ.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $message = 'Dữ liệu không hợp lệ.';
        if ($errors->has('admin_reject_reason')) {
            $reasonErrors = $errors->get('admin_reject_reason');
            if (in_array('Vui lòng nhập lý do từ chối.', $reasonErrors, true)) {
                $message = 'Vui lòng nhập lý do từ chối.';
            }
        }
        throw new HttpResponseException(
            ApiResponse::error($message, $errors->toArray(), 422)
        );
    }
}