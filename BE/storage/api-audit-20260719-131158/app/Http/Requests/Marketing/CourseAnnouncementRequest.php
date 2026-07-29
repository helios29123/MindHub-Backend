<?php

namespace App\Http\Requests\Marketing;

use App\Http\Requests\BaseApiRequest;

class CourseAnnouncementRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
