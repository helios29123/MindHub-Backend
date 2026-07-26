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
        $metadata = data_get($this->resource, 'metadata', []);
        $payout = data_get($this->resource, 'payout');

        $emailVerified = !is_null(data_get($user, 'email_verified_at'));
        $phoneValue = data_get($user, 'phone');
        $phoneVerified = !empty($phoneValue);

        $socialLinks = data_get($metadata, 'social_links', [
            'website' => null,
            'facebook' => null,
            'linkedin' => null,
            'youtube' => null,
        ]);

        $quickSettings = data_get($metadata, 'quick_settings', [
            'email_notifications' => true,
            'sms_alerts' => true,
        ]);

        $avatarUrl = data_get($user, 'avatar_url') ?? data_get($metadata, 'avatar_url');

        $userId = (int) data_get($user, 'id');
        $avgRating = \Illuminate\Support\Facades\DB::table('course_reviews')
            ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
            ->join('courses', 'courses.id', '=', 'orders.course_id')
            ->where('courses.instructor_id', $userId)
            ->avg('course_reviews.rating');
        $reputationScore = $avgRating !== null ? round((float)$avgRating, 1) : null;
        $isLocked = (bool) data_get($user, 'locked', false);

        return [
            'id' => $userId,
            'full_name' => data_get($user, 'full_name'),
            'email' => data_get($user, 'email'),
            'phone' => $phoneValue,
            'expertise' => data_get($profile, 'expertise') ?? 'Lập trình Web',
            'bio' => data_get($profile, 'bio'),
            'avatar' => $avatarUrl,
            'avatar_url' => $avatarUrl,
            'social_links' => $socialLinks,
            'quick_settings' => $quickSettings,
            'verification' => [
                'email_verified' => $emailVerified,
                'phone_verified' => $phoneVerified,
            ],
            'account_status' => data_get($user, 'status') === 'active' ? 'Đang hoạt động' : (data_get($user, 'status') === 'locked' ? 'Đã khóa' : 'Tạm đình chỉ'),
            'policy_compliance' => $isLocked ? 'Bị vi phạm' : 'Tốt',
            'reputation_score' => $reputationScore,
            'payout_shortcut' => [
                'bank_name' => data_get($payout, 'account_name') ?? data_get($payout, 'provider') ?? 'Techcombank',
                'account_number' => data_get($payout, 'account_number') ? '**** ' . substr(data_get($payout, 'account_number'), -4) : '**** 1234',
                'is_default' => (bool) data_get($payout, 'is_default', true),
            ],
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