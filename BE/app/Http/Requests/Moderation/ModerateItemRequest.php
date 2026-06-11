<?php

namespace App\Http\Requests\Moderation;

use App\Http\Requests\BaseApiRequest;

class ModerateItemRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', 'in:comment,review'],
            'status' => ['required', 'string', 'in:visible,hidden,deleted'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
