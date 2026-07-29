<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstructorCreditOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_package_id' => ['required', 'integer', 'exists:course_credit_packages,id'],
        ];
    }
}
