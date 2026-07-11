<?php

namespace App\Services\Instructor;

use App\Exceptions\BusinessException;
use App\Models\InstructorProfile;
use App\Models\User;
use App\Repositories\Instructor\InstructorProfileRepository;
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
        private readonly InstructorProfileRepository $repository
    ) {
    }

    public function getProfile(User $authUser): array
    {
        $user = $this->getOwnedInstructor($authUser);
        $profile = $this->repository->findProfileByUserId((int) $user->id);

        return [
            'user' => $user,
            'profile' => $profile,
            'completion' => $this->calculateCompletion($profile),
        ];
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

        if (!$user) {
            throw new BusinessException(
                'Không tìm thấy hồ sơ giảng viên hoặc bạn không có quyền thao tác.',
                404
            );
        }

        return $user;
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