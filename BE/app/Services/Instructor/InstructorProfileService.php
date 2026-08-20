<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\InstructorProfile;
use App\Models\User;
use App\Repositories\Instructor\InstructorProfileRepository;
use App\Services\Storage\CloudinaryService;
use Illuminate\Support\Facades\DB;

final class InstructorProfileService
{
    private const COMPLETION_FIELDS = [
        'bio' => 'Giới thiệu bản thân',
        'expertise' => 'Chuyên môn',
        'experience_years' => 'Số năm kinh nghiệm',
        'level' => 'Cấp độ giảng viên',
    ];

    public function __construct(
        private readonly InstructorProfileRepository $repository,
        private readonly CloudinaryService $cloudinaryService,
    ) {
    }

    public function getProfile(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $profile = $this->repository->findProfileByUserId((int) $user->id);
        $metadata = $this->getMetadata($user);
        $payout = \App\Models\PayoutAccount::where('user_id', $user->id)->first();

        return [
            'user' => $user,
            'profile' => $profile,
            'metadata' => $metadata,
            'payout' => $payout,
            'completion' => $this->calculateCompletion($profile),
        ];
    }

    public function updateProfile(User $authUser, array $data): array
    {
        $user = $this->getOwnedInstructor($authUser);

        return DB::transaction(function () use ($user, $data) {
            // Update User full_name & phone
            $userUpdate = [];
            if (!empty($data['full_name'])) {
                $userUpdate['full_name'] = trim((string) $data['full_name']);
            }
            if (array_key_exists('phone', $data)) {
                $userUpdate['phone'] = $data['phone'] ? trim((string) $data['phone']) : null;
            }
            if (!empty($userUpdate)) {
                $this->repository->updateAccount($user, $userUpdate);
            }

            // Update InstructorProfile bio & expertise
            $profileUpdate = [];
            if (array_key_exists('bio', $data)) {
                $profileUpdate['bio'] = $data['bio'];
            }
            if (array_key_exists('expertise', $data)) {
                $profileUpdate['expertise'] = $data['expertise'];
            }
            if (!empty($profileUpdate)) {
                $this->repository->updateOrCreateProfile((int) $user->id, $profileUpdate);
            }

            // Update social_links in metadata
            if (isset($data['social_links']) && is_array($data['social_links'])) {
                $this->updateMetadata($user, ['social_links' => $data['social_links']]);
            }

            return $this->getProfile($user->refresh());
        });
    }

    public function uploadAvatar(User $authUser, \Illuminate\Http\UploadedFile $file): string
    {
        $user = $this->getOwnedInstructor($authUser);

        // Upload new avatar first.
        // Only delete the old Cloudinary asset after the new upload succeeds.
        $uploaded = $this->cloudinaryService->uploadImage(
            $file,
            'mindhub/avatars'
        );

        $oldPublicId = $user->avatar_public_id;

        $user->avatar_url = $uploaded['url'];
        $user->avatar_public_id = $uploaded['public_id'];
        $user->save();

        $this->updateMetadata($user, [
            'avatar_url' => $uploaded['url'],
        ]);

        // Delete previous Cloudinary avatar after the new one is persisted.
        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        return $uploaded['url'];
    }

    public function selectAvatarPreset(User $authUser, string $presetId): string
    {
        $user = $this->getOwnedInstructor($authUser);

        $presets = [
            'avatar_01' => 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?: 'MindHub User') . '&background=007A64&color=fff&bold=true',
            'avatar_02' => 'https://ui-avatars.com/api/?name=Instructor&background=121b4b&color=fff&bold=true',
            'avatar_03' => 'https://ui-avatars.com/api/?name=Student&background=0284c7&color=fff&bold=true',
            'avatar_04' => 'https://ui-avatars.com/api/?name=Learner&background=7c3aed&color=fff&bold=true',
            'avatar_05' => 'https://ui-avatars.com/api/?name=Pro&background=d97706&color=fff&bold=true',
        ];

        if (! isset($presets[$presetId])) {
            throw new BusinessException('Mẫu ảnh đại diện không hợp lệ.', 422);
        }

        $avatarUrl = $presets[$presetId];

        $oldPublicId = $user->avatar_public_id;

        $user->avatar_url = $avatarUrl;
        $user->avatar_public_id = null;
        $user->save();

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        $this->updateMetadata($user, ['avatar_url' => $avatarUrl]);

        return $avatarUrl;
    }

    public function deleteAvatar(User $authUser): ?string
    {
        $user = $this->getOwnedInstructor($authUser);

        $oldPublicId = $user->avatar_public_id;

        if (!empty($oldPublicId)) {
            $this->cloudinaryService->deleteImage($oldPublicId);
        }

        $user->avatar_url = null;
        $user->avatar_public_id = null;
        $user->save();

        $this->updateMetadata($user, [
            'avatar_url' => null,
        ]);

        return null;
    }

    public function getPreferences(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $meta = $this->getMetadata($user);

        return data_get($meta, 'quick_settings', [
            'email_notifications' => true,
            'sms_alerts' => true,
        ]);
    }

    public function updatePreferences(User $authUser, array $data): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $meta = $this->getMetadata($user);
        $currentSettings = data_get($meta, 'quick_settings', [
            'email_notifications' => true,
            'sms_alerts' => true,
        ]);

        // Validation for SMS Alerts: require non-empty phone
        if (isset($data['sms_alerts']) && (bool)$data['sms_alerts'] === true) {
            if (empty($user->phone)) {
                throw new BusinessException('Bạn cần cập nhật số điện thoại trước khi bật SMS Alerts.', 422);
            }
        }

        $updatedSettings = array_merge($currentSettings, $data);
        $this->updateMetadata($user, ['quick_settings' => $updatedSettings]);

        return $updatedSettings;
    }

    public function sendPasswordOtp(User $authUser, array $data): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $currentPass = $data['current_password'] ?? '';
        $newPass = $data['password'] ?? $data['new_password'] ?? '';
        $confirmPass = $data['password_confirmation'] ?? $data['confirm_password'] ?? '';

        if (!\Illuminate\Support\Facades\Hash::check($currentPass, (string) $user->password_hash)) {
            throw new BusinessException('Mật khẩu hiện tại không chính xác.', 422);
        }

        if (empty($newPass) || strlen($newPass) < 8) {
            throw new BusinessException('Mật khẩu mới phải có từ 8 ký tự trở lên.', 422);
        }

        if ($newPass !== $confirmPass) {
            throw new BusinessException('Mật khẩu mới và xác nhận mật khẩu không khớp.', 422);
        }

        if (\Illuminate\Support\Facades\Hash::check($newPass, (string) $user->password_hash)) {
            throw new BusinessException('Mật khẩu mới không được trùng với mật khẩu hiện tại.', 422);
        }

        // Rate limit: Max 3 OTP requests per 15 mins
        $recentCount = \App\Models\UserOtp::where('user_id', $user->id)
            ->where('purpose', 'change_password')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentCount >= 3) {
            throw new BusinessException('Bạn đã gửi yêu cầu mã OTP quá 3 lần trong 15 phút. Vui lòng thử lại sau.', 429);
        }

        // Generate OTP
        $otpCode = \App\Models\UserOtp::generateOtp((int)$user->id, 'change_password', 300);

        // Try sending email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\PasswordChangeOtpMail($otpCode, $user->full_name)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send OTP mail to ' . $user->email . ': ' . $e->getMessage());
        }

        // Mask email for response (e.g. instructor1@mindhub.test -> in****@mindhub.test)
        $parts = explode('@', $user->email);
        $namePart = $parts[0];
        $domainPart = $parts[1] ?? 'mindhub.test';
        $maskedName = strlen($namePart) > 2 ? substr($namePart, 0, 2) . '****' : $namePart . '****';
        $maskedEmail = $maskedName . '@' . $domainPart;

        return [
            'expires_in' => 300,
            'resend_after' => 60,
            'masked_email' => $maskedEmail,
        ];
    }

    public function changePassword(User $authUser, array $data): void
    {
        $user = $this->getOwnedInstructor($authUser);
        $currentPass = $data['current_password'] ?? '';
        $newPass = $data['password'] ?? $data['new_password'] ?? '';
        $confirmPass = $data['password_confirmation'] ?? $data['confirm_password'] ?? '';
        $otp = $data['otp'] ?? '';

        if (empty($otp)) {
            throw new BusinessException('Vui lòng nhập mã xác minh OTP.', 422);
        }

        if (!\Illuminate\Support\Facades\Hash::check($currentPass, (string) $user->password_hash)) {
            throw new BusinessException('Mật khẩu hiện tại không chính xác.', 422);
        }

        if (empty($newPass) || strlen($newPass) < 8) {
            throw new BusinessException('Mật khẩu mới phải có từ 8 ký tự trở lên.', 422);
        }

        if ($newPass !== $confirmPass) {
            throw new BusinessException('Mật khẩu mới và xác nhận mật khẩu không khớp.', 422);
        }

        if (\Illuminate\Support\Facades\Hash::check($newPass, (string) $user->password_hash)) {
            throw new BusinessException('Mật khẩu mới không được trùng với mật khẩu hiện tại.', 422);
        }

        // Verify and consume OTP
        \App\Models\UserOtp::verifyOtp((int)$user->id, $otp, 'change_password');

        // Update Password
        $user->password_hash = \Illuminate\Support\Facades\Hash::make($newPass);
        $user->save();

        // Regenerate Session ID
        try {
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }
        } catch (\Throwable $e) {
            // Ignore if session not active in test environment
        }
    }

    public function getSessions(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $currentSessionId = request()->hasSession() ? request()->session()->getId() : null;

        try {
            $sessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $user->id)
                ->get();

            if ($sessions->count() > 0) {
                return $sessions->map(function ($s) use ($currentSessionId) {
                    $ua = $s->user_agent ?? '';
                    $platform = 'Windows 11';
                    if (str_contains($ua, 'Mac')) $platform = 'macOS';
                    else if (str_contains($ua, 'Linux')) $platform = 'Linux';
                    else if (str_contains($ua, 'Android')) $platform = 'Android';
                    else if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $platform = 'iOS';

                    $device = 'Chrome Browser';
                    if (str_contains($ua, 'Firefox')) $device = 'Firefox Browser';
                    else if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) $device = 'Safari Browser';
                    else if (str_contains($ua, 'Edg')) $device = 'Edge Browser';

                    return [
                        'id' => (string) $s->id,
                        'device' => $device,
                        'platform' => $platform,
                        'ip_address' => $s->ip_address ?? '127.0.0.1',
                        'last_activity_at' => \Carbon\Carbon::createFromTimestamp($s->last_activity)->toDateTimeString(),
                        'is_current' => $currentSessionId ? ($s->id === $currentSessionId) : true,
                    ];
                })->values()->toArray();
            }
        } catch (\Throwable $e) {
            // Fallback if table doesn't exist
        }

        // Fallback for local dev/current request
        return [
            [
                'id' => $currentSessionId ?: 'current-session-id',
                'device' => 'Chrome Browser',
                'platform' => 'Windows 11',
                'ip_address' => request()->ip() ?: '127.0.0.1',
                'last_activity_at' => now()->toDateTimeString(),
                'is_current' => true,
            ]
        ];
    }

    public function revokeOtherSessions(User $authUser): void
    {
        $user = $this->getOwnedInstructor($authUser);
        $currentSessionId = request()->hasSession() ? request()->session()->getId() : null;

        try {
            $query = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $user->id);
            if ($currentSessionId) {
                $query->where('id', '!=', $currentSessionId);
            }
            $query->delete();
        } catch (\Throwable $e) {
            // Ignore if driver is not database
        }
    }

    public function revokeSession(User $authUser, string $sessionId): void
    {
        $user = $this->getOwnedInstructor($authUser);
        $currentSessionId = request()->hasSession() ? request()->session()->getId() : null;

        if ($currentSessionId && $sessionId === $currentSessionId) {
            throw new BusinessException('Không thể thu hồi phiên đăng nhập hiện tại.', 400);
        }

        try {
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', $sessionId)
                ->delete();
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function getPrivacy(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $meta = $this->getMetadata($user);

        return data_get($meta, 'privacy_settings', [
            'profile_visibility' => 'public',
            'show_email' => false,
            'show_phone' => false,
            'show_social_links' => true,
            'allow_messages' => true,
        ]);
    }

    public function updatePrivacy(User $authUser, array $data): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $meta = $this->getMetadata($user);
        $currentPrivacy = data_get($meta, 'privacy_settings', [
            'profile_visibility' => 'public',
            'show_email' => false,
            'show_phone' => false,
            'show_social_links' => true,
            'allow_messages' => true,
        ]);

        $updatedPrivacy = array_merge($currentPrivacy, $data);
        $this->updateMetadata($user, ['privacy_settings' => $updatedPrivacy]);

        return $updatedPrivacy;
    }

    public function getAccountStatus(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);

        $avgRating = \Illuminate\Support\Facades\DB::table('course_reviews')
            ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
            ->join('courses', 'courses.id', '=', 'orders.course_id')
            ->where('courses.instructor_id', $user->id)
            ->avg('course_reviews.rating');

        $reputationScore = $avgRating !== null ? round((float)$avgRating, 1) : null;
        $statusText = $user->status === 'active' ? 'Đang hoạt động' : ($user->status === 'locked' ? 'Đã khóa' : 'Tạm đình chỉ');

        return [
            'account_status' => $statusText,
            'email_verified' => $user->email_verified_at !== null,
            'phone_verified' => !empty($user->phone),
            'policy_compliant' => $user->locked ? 'Bị vi phạm' : 'Tốt',
            'reputation_score' => $reputationScore,
        ];
    }

    private function getMetadata(User $user): array
    {
        $raw = $user->locked_reason;
        if ($raw && str_starts_with(trim($raw), '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    private function updateMetadata(User $user, array $data): void
    {
        $existing = $this->getMetadata($user);
        $merged = array_replace_recursive($existing, $data);
        $json = json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        DB::table('users')->where('id', $user->id)->update(['locked_reason' => $json]);
        $user->refresh();
    }

    public function updateAccount(User $authUser, array $data): User
    {
        $user = $this->getOwnedInstructor($authUser);

        $allowedData = [
            'full_name' => trim((string) $data['full_name']),
        ];

        return DB::transaction(
            fn (): User => $this->repository->updateAccount(
                $user,
                $allowedData
            )
        );
    }

    public function updateIntroduction(
        User $authUser,
        array $data
    ): array {
        $user = $this->getOwnedInstructor($authUser);

        $profile = DB::transaction(
            fn (): InstructorProfile => $this->repository->updateOrCreateProfile(
                (int) $user->id,
                [
                    'bio' => $data['bio'] ?? null,
                ]
            )
        );

        return [
            'profile' => $profile,
            'completion' => $this->calculateCompletion($profile),
        ];
    }

    public function updateExpertise(
        User $authUser,
        array $data
    ): array {
        $user = $this->getOwnedInstructor($authUser);

        $allowed = array_intersect_key(
            $data,
            array_flip([
                'expertise',
                'experience_years',
                'level',
            ])
        );

        $profile = DB::transaction(
            fn (): InstructorProfile => $this->repository->updateOrCreateProfile(
                (int) $user->id,
                $allowed
            )
        );

        return [
            'profile' => $profile,
            'completion' => $this->calculateCompletion($profile),
        ];
    }

    public function getCompletion(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $profile = $this->repository->findProfileByUserId((int) $user->id);

        return $this->calculateCompletion($profile);
    }

    private function getOwnedInstructor(User $authUser): User
    {
        $user = $this->repository->findInstructorUser((int) $authUser->id);
        return $user ?: $authUser;
    }

    private function calculateCompletion(
        ?InstructorProfile $profile
    ): array {
        $missingNames = [];
        $missingFields = [];
        $completed = 0;

        foreach (self::COMPLETION_FIELDS as $field => $label) {
            $value = $profile?->{$field};

            $isCompleted = match ($field) {
                'experience_years' => $profile !== null && $value !== null,
                default => $value !== null
                    && trim((string) $value) !== '',
            };

            if ($isCompleted) {
                $completed++;
                continue;
            }

            $missingNames[] = $field;
            $missingFields[] = [
                'field' => $field,
                'label' => $label,
            ];
        }

        return [
            'completed_fields' => $completed,
            'total_fields' => count(self::COMPLETION_FIELDS),
            'is_completed' => $completed === count(self::COMPLETION_FIELDS),
            'missing_field_names' => $missingNames,
            'missing_fields' => $missingFields,
        ];
    }
}
