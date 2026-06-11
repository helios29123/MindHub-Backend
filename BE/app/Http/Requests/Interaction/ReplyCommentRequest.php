<?php

namespace App\Http\Requests\Interaction;

use App\Http\Requests\BaseApiRequest;

class ReplyCommentRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }
}
