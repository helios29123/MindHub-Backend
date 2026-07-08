<?php
namespace App\Http\Requests\Instructor;
use Illuminate\Foundation\Http\FormRequest;
final class LessonProgressQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'group_by_section' => ['nullable', 'boolean'],
        ];
    }
}