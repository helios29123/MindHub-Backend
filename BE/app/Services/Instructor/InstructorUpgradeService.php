<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\Instructor\InstructorUpgradeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InstructorUpgradeService
{
    public function __construct(
        private readonly InstructorUpgradeRepository $instructorUpgradeRepository
    ) {
    }

    public function myApplication(User $user): array
    {
        return $this->instructorUpgradeRepository->buildApplicationData((int) $user->id);
    }

    public function store(User $user, array $data): array
    {
        $this->ensureLearnerCanApply($user);
        $this->ensurePhoneAvailable($user, $data['phone'] ?? null);

        $profile = $this->instructorUpgradeRepository->findProfileByUserId((int) $user->id);
        $payout = $this->instructorUpgradeRepository->findLatestPayoutByUserId((int) $user->id);

        if ($profile && $payout?->status === 'pending_verification') {
            throw new BusinessException('Bạn đã gửi yêu cầu nâng cấp giảng viên và đang chờ duyệt.', 409);
        }

        if ($profile && $payout?->status === 'rejected') {
            throw new BusinessException('Yêu cầu trước đó đã bị từ chối. Vui lòng dùng API cập nhật để gửi lại.', 409);
        }

        if ($profile || $payout) {
            throw new BusinessException('Tài khoản đã có dữ liệu yêu cầu nâng cấp giảng viên.', 409);
        }

        DB::transaction(function () use ($user, $data) {
            if (! empty($data['phone']) && $data['phone'] !== $user->phone) {
                $this->instructorUpgradeRepository->updateUser($user, [
                    'phone' => $data['phone'],
                ]);
            }

            $this->instructorUpgradeRepository->createProfile([
                'user_id' => $user->id,
                'bio' => $data['bio'],
                'expertise' => $data['expertise'],
                'experience_years' => $data['experience_years'],
                'level' => $data['level'],
            ]);

            $this->instructorUpgradeRepository->createPayout([
                'user_id' => $user->id,
                'provider' => $data['bank_provider'],
                'account_number' => $data['bank_account_number'],
                'account_name' => $data['bank_account_name'],
                'connected_at' => null,
                'status' => 'pending_verification',
            ]);
        });

        return $this->instructorUpgradeRepository->buildApplicationData((int) $user->id);
    }

    public function update(User $user, array $data): array
    {
        $this->ensureLearnerCanApply($user);
        $this->ensurePhoneAvailable($user, $data['phone'] ?? null);

        $profile = $this->instructorUpgradeRepository->findProfileByUserId((int) $user->id);
        $payout = $this->instructorUpgradeRepository->findLatestPayoutByUserId((int) $user->id);

        if (! $profile || ! $payout) {
            throw new BusinessException('Bạn chưa có yêu cầu nâng cấp giảng viên để cập nhật.', 404);
        }

        if ($payout->status === 'pending_verification') {
            throw new BusinessException('Yêu cầu nâng cấp giảng viên đang chờ duyệt, chưa thể gửi lại.', 409);
        }

        if ($payout->status === 'active') {
            throw new BusinessException('Tài khoản đã được duyệt thành giảng viên.', 409);
        }

        if ($payout->status !== 'rejected') {
            throw new BusinessException('Trạng thái yêu cầu nâng cấp không cho phép cập nhật.', 409);
        }

        DB::transaction(function () use ($user, $profile, $payout, $data) {
            if (! empty($data['phone']) && $data['phone'] !== $user->phone) {
                $this->instructorUpgradeRepository->updateUser($user, [
                    'phone' => $data['phone'],
                ]);
            }

            $this->instructorUpgradeRepository->updateProfile($profile, [
                'bio' => $data['bio'],
                'expertise' => $data['expertise'],
                'experience_years' => $data['experience_years'],
                'level' => $data['level'],
            ]);

            $this->instructorUpgradeRepository->updatePayout($payout, [
                'provider' => $data['bank_provider'],
                'account_number' => $data['bank_account_number'],
                'account_name' => $data['bank_account_name'],
                'connected_at' => null,
                'status' => 'pending_verification',
            ]);
        });

        return $this->instructorUpgradeRepository->buildApplicationData((int) $user->id);
    }

    public function adminIndex(int $perPage = 15): LengthAwarePaginator
    {
        return $this->instructorUpgradeRepository->paginatePendingApplications($perPage);
    }

    public function adminShow(int $userId): array
    {
        $application = $this->instructorUpgradeRepository->buildApplicationData($userId);

        if (! $application) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }

        if ($application['application_status'] === 'none') {
            throw new BusinessException('Người dùng chưa gửi yêu cầu nâng cấp giảng viên.', 404);
        }

        return $application;
    }

    public function approve(int $userId): array
    {
        $user = $this->instructorUpgradeRepository->findUserById($userId);

        if (! $user) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }

        if ($user->role === 'instructor') {
            throw new BusinessException('Tài khoản này đã là giảng viên.', 409);
        }

        if ($user->role !== 'learner') {
            throw new BusinessException('Chỉ tài khoản learner mới có thể nâng cấp thành giảng viên.', 400);
        }

        if ($user->status !== 'active' || ! $user->email_verified_at) {
            throw new BusinessException('Tài khoản chưa active hoặc chưa xác thực email.', 400);
        }

        $profile = $this->instructorUpgradeRepository->findProfileByUserId($userId);
        $pendingPayout = $this->instructorUpgradeRepository->findPendingPayoutByUserId($userId);

        if (! $profile) {
            throw new BusinessException('Tài khoản chưa có hồ sơ giảng viên.', 400);
        }

        if (! $pendingPayout) {
            throw new BusinessException('Tài khoản chưa có yêu cầu payout đang chờ duyệt.', 400);
        }

        DB::transaction(function () use ($user, $pendingPayout) {
            $this->instructorUpgradeRepository->updateUser($user, [
                'role' => 'instructor',
            ]);

            $this->instructorUpgradeRepository->updatePayout($pendingPayout, [
                'status' => 'active',
                'connected_at' => now(),
            ]);
        });

        return $this->instructorUpgradeRepository->buildApplicationData($userId);
    }

    public function reject(int $userId): array
    {
        $user = $this->instructorUpgradeRepository->findUserById($userId);

        if (! $user) {
            throw new BusinessException('Không tìm thấy người dùng.', 404);
        }

        if ($user->role !== 'learner') {
            throw new BusinessException('Chỉ có thể từ chối yêu cầu của tài khoản learner.', 400);
        }

        $profile = $this->instructorUpgradeRepository->findProfileByUserId($userId);
        $pendingPayout = $this->instructorUpgradeRepository->findPendingPayoutByUserId($userId);

        if (! $profile) {
            throw new BusinessException('Tài khoản chưa có hồ sơ giảng viên.', 400);
        }

        if (! $pendingPayout) {
            throw new BusinessException('Không có yêu cầu nâng cấp nào đang chờ duyệt.', 400);
        }

        $this->instructorUpgradeRepository->updatePayout($pendingPayout, [
            'status' => 'rejected',
            'connected_at' => null,
        ]);

        return $this->instructorUpgradeRepository->buildApplicationData($userId);
    }

    private function ensureLearnerCanApply(User $user): void
    {
        if ($user->role !== 'learner') {
            throw new BusinessException('Chỉ học viên mới được gửi yêu cầu nâng cấp giảng viên.', 403);
        }

        if ($user->status !== 'active' || $user->locked) {
            throw new BusinessException('Tài khoản không được phép gửi yêu cầu nâng cấp giảng viên.', 403);
        }

        if (! $user->email_verified_at) {
            throw new BusinessException('Vui lòng xác thực email trước khi gửi yêu cầu nâng cấp giảng viên.', 403);
        }
    }

    private function ensurePhoneAvailable(User $user, ?string $phone): void
    {
        if (empty($user->phone) && empty($phone)) {
            throw new BusinessException('Tài khoản cần có số điện thoại trước khi gửi yêu cầu nâng cấp giảng viên.', 422, [
                'phone' => ['Số điện thoại không được để trống.'],
            ]);
        }

        if (! empty($phone) && $phone !== $user->phone) {
            if ($this->instructorUpgradeRepository->existsPhoneExceptUser($phone, (int) $user->id)) {
                throw new BusinessException('Số điện thoại đã được sử dụng.', 409, [
                    'phone' => ['Số điện thoại đã được sử dụng.'],
                ]);
            }
        }
    }
}
