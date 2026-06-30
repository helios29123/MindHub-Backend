<?php

namespace App\Repositories\Instructor;

use App\Models\InstructorProfile;
use App\Models\PayoutAccount;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstructorUpgradeRepository
{
    public function findUserById(int $userId): ?User
    {
        return User::find($userId);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function existsPhoneExceptUser(string $phone, int $exceptUserId): bool
    {
        return User::where('phone', $phone)
            ->where('id', '!=', $exceptUserId)
            ->exists();
    }

    public function findProfileByUserId(int $userId): ?InstructorProfile
    {
        return InstructorProfile::where('user_id', $userId)->first();
    }

    public function createProfile(array $data): InstructorProfile
    {
        return InstructorProfile::create($data);
    }

    public function updateProfile(InstructorProfile $profile, array $data): InstructorProfile
    {
        $profile->fill($data);
        $profile->save();

        return $profile->refresh();
    }

    public function findLatestPayoutByUserId(int $userId): ?PayoutAccount
    {
        return PayoutAccount::where('user_id', $userId)
            ->orderByDesc('id')
            ->first();
    }

    public function findPendingPayoutByUserId(int $userId): ?PayoutAccount
    {
        return PayoutAccount::where('user_id', $userId)
            ->where('status', 'pending_verification')
            ->orderByDesc('id')
            ->first();
    }

    public function createPayout(array $data): PayoutAccount
    {
        return PayoutAccount::create($data);
    }

    public function updatePayout(PayoutAccount $payoutAccount, array $data): PayoutAccount
    {
        $payoutAccount->fill($data);
        $payoutAccount->save();

        return $payoutAccount->refresh();
    }

    public function paginatePendingApplications(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = DB::table('users as u')
            ->join('instructor_profiles as ip', 'ip.user_id', '=', 'u.id')
            ->join('payout_accounts as pa', 'pa.user_id', '=', 'u.id')
            ->where('u.role', 'learner')
            ->where('pa.status', 'pending_verification')
            ->whereNull('u.deleted_at')
            ->select([
                'u.id as user_id',
                'u.full_name',
                'u.email',
                'u.phone',
                'u.role',
                'u.status as user_status',
                'u.email_verified_at',
                'ip.bio',
                'ip.expertise',
                'ip.experience_years',
                'ip.level',
                'ip.created_at as submitted_at',
                'pa.id as payout_id',
                'pa.provider',
                'pa.account_number',
                'pa.account_name',
                'pa.status as payout_status',
                'pa.connected_at',
            ])
            ->orderByDesc('ip.created_at')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function ($row) {
            return $this->mapJoinedRowToApplication((array) $row);
        });

        return $paginator;
    }

    public function buildApplicationData(int $userId): ?array
    {
        $user = $this->findUserById($userId);

        if (! $user) {
            return null;
        }

        $profile = $this->findProfileByUserId($userId);
        $payout = $this->findLatestPayoutByUserId($userId);

        return [
            'application_status' => $this->determineApplicationStatus($user, $profile, $payout),
            'submitted_at' => $profile?->created_at?->toISOString(),
            'review_note' => $this->buildReviewNote($user, $profile, $payout),

            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
            ],

            'instructor_profile' => $profile ? [
                'bio' => $profile->bio,
                'expertise' => $profile->expertise,
                'experience_years' => $profile->experience_years,
                'level' => $profile->level,
                'created_at' => $profile->created_at,
                'updated_at' => $profile->updated_at,
            ] : null,

            'payout_account' => $payout ? [
                'id' => $payout->id,
                'provider' => $payout->provider,
                'account_number' => $payout->account_number,
                'account_name' => $payout->account_name,
                'status' => $payout->status,
                'connected_at' => $payout->connected_at,
                'created_at' => $payout->created_at,
                'updated_at' => $payout->updated_at,
            ] : null,
        ];
    }

    private function determineApplicationStatus(User $user, ?InstructorProfile $profile, ?PayoutAccount $payout): string
    {
        if (! $profile && ! $payout) {
            return 'none';
        }

        if ($user->role === 'instructor' && $payout?->status === 'active') {
            return 'approved';
        }

        if ($profile && $payout?->status === 'pending_verification') {
            return 'pending';
        }

        if ($profile && $payout?->status === 'rejected') {
            return 'rejected';
        }

        if ($profile && ! $payout) {
            return 'missing_payout';
        }

        return 'unknown';
    }

    private function buildReviewNote(User $user, ?InstructorProfile $profile, ?PayoutAccount $payout): ?string
    {
        if (! $profile && ! $payout) {
            return 'Bạn chưa gửi yêu cầu nâng cấp giảng viên.';
        }

        if ($profile && $payout?->status === 'pending_verification') {
            return 'Yêu cầu đang chờ admin duyệt.';
        }

        if ($profile && $payout?->status === 'rejected') {
            return 'Yêu cầu đã bị từ chối. Bạn có thể cập nhật thông tin và gửi lại.';
        }

        if ($user->role === 'instructor' && $payout?->status === 'active') {
            return 'Tài khoản đã được nâng cấp thành giảng viên.';
        }

        return null;
    }

    private function mapJoinedRowToApplication(array $row): array
    {
        return [
            'application_status' => 'pending',
            'submitted_at' => $row['submitted_at'] ?? null,
            'review_note' => 'Yêu cầu đang chờ admin duyệt.',

            'user' => [
                'id' => $row['user_id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'role' => $row['role'],
                'status' => $row['user_status'],
                'email_verified_at' => $row['email_verified_at'],
            ],

            'instructor_profile' => [
                'bio' => $row['bio'],
                'expertise' => $row['expertise'],
                'experience_years' => $row['experience_years'],
                'level' => $row['level'],
            ],

            'payout_account' => [
                'id' => $row['payout_id'],
                'provider' => $row['provider'],
                'account_number' => $row['account_number'],
                'account_name' => $row['account_name'],
                'status' => $row['payout_status'],
                'connected_at' => $row['connected_at'],
            ],
        ];
    }
}
