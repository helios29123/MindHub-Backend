<?php
namespace App\Http\Requests\Instructor;
use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
final class UploadLessonAssetRequest extends FormRequest
{
    private const ALLOWED_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'ppt',
        'pptx',
        'xls',
        'xlsx',
        'txt',
        'csv',
        'zip',
        'rar',
        '7z',
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }
                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                        $fail('Định dạng tài liệu không được hỗ trợ.');
                    }
                },
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip,rar,7z,jpg,jpeg,png,webp',
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn tài liệu cần upload.',
            'file.file' => 'Tài liệu upload không hợp lệ.',
            'file.max' => 'Dung lượng tài liệu không được vượt quá 50MB.',
            'file.mimes' => 'Định dạng tài liệu không được hỗ trợ.',
            'title.string' => 'Tiêu đề tài liệu không hợp lệ.',
            'title.max' => 'Tiêu đề tài liệu không được vượt quá 255 ký tự.',
            'note.string' => 'Ghi chú tài liệu không hợp lệ.',
            'note.max' => 'Ghi chú tài liệu không được vượt quá 2000 ký tự.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ.',
                $validator->errors()->toArray(),
                422
            )
        );
    }
}