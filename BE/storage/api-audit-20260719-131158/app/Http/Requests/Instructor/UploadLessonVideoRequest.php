<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

final class UploadLessonVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime',
                'max:204800',
            ],
            'video_duration_seconds' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => 'Vui lòng chọn video bài học.',
            'video.file' => 'Video bài học phải là một file hợp lệ.',
            'video.mimetypes' => 'Video bài học chỉ hỗ trợ định dạng mp4, webm hoặc mov.',
            'video.max' => 'Dung lượng video bài học không được vượt quá 200MB.',
            'video_duration_seconds.integer' => 'Thời lượng video phải là số nguyên.',
            'video_duration_seconds.min' => 'Thời lượng video không được nhỏ hơn 0.',
        ];
    }
}