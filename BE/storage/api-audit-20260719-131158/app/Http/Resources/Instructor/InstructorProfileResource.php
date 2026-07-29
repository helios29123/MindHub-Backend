<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = data_get($this->resource, 'user');
        $profile = data_get($this->resource, 'profile');
        $completion = data_get($this->resource, 'completion', []);

        return [
            'account' => [
                'id' => (int) data_get($user, 'id'),
                'full_name' => data_get($user, 'full_name'),
                'email' => data_get($user, 'email'),
                'phone' => data_get($user, 'phone'),
                'role' => data_get($user, 'role'),
                'status' => data_get($user, 'status'),
                'email_verified_at' => $this->dateValue(
                    data_get($user, 'email_verified_at')
                ),
                'last_login_at' => $this->dateValue(
                    data_get($user, 'last_login_at')
                ),
            ],
            'profile' => [
                'bio' => data_get($profile, 'bio'),
                'expertise' => data_get($profile, 'expertise'),
                'experience_years' => data_get($profile, 'experience_years'),
                'level' => data_get($profile, 'level'),
            ],
            'completion' => [
                'completed_fields' => (int) data_get($completion, 'completed_fields', 0),
                'total_fields' => (int) data_get($completion, 'total_fields', 4),
                'is_completed' => (bool) data_get($completion, 'is_completed', false),
                'missing_fields' => data_get($completion, 'missing_field_names', []),
            ],
        ];
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}