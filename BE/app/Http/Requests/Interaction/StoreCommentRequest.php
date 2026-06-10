<?php

namespace App\Http\Requests\Interaction;

use App\Http\Requests\BaseApiRequest;

class StoreCommentRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }
}
