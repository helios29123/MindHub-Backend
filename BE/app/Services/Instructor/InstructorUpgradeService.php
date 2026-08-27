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

        // Send Email & Notification to Admin
        try {
            $adminEmail = env('ADMIN_EMAIL', config('mail.admin_address', 'dominhdang3010@gmail.com'));
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(
                new \App\Mail\InstructorUpgradeRequestedMail($user, $data)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send admin instructor upgrade email: ' . $e->getMessage());
        }

        try {
            $adminUsers = User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'instructor_upgrade_request',
                    'title' => 'Yêu cầu đăng ký Giảng viên mới',
                    'message' => "Học viên {$user->full_name} ({$user->email}) đã gửi yêu cầu đăng ký làm Giảng viên.",
                    'action_url' => '/admin/instructors/upgrade-requests',
                    'channel' => 'database',
                ]);
            }
        } catch (\Throwable $e) {}

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

    public function adminIndexReport(array $queryParams): array
    {
        $latestPayoutQuery = DB::table('payout_accounts')
            ->select('user_id', DB::raw('MAX(id) as payout_id'))
            ->groupBy('user_id');

        $baseQuery = DB::table('users as u')
            ->join('instructor_profiles as ip', 'ip.user_id', '=', 'u.id')
            ->leftJoinSub($latestPayoutQuery, 'latest_pa', function ($join): void {
                $join->on('latest_pa.user_id', '=', 'u.id');
            })
            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id');

        $total = (clone $baseQuery)->count();
        $pending = (clone $baseQuery)->where('pa.status', 'pending_verification')->count();
        $approved = (clone $baseQuery)->where('u.role', 'instructor')->where('pa.status', 'active')->count();
        $rejected = (clone $baseQuery)->where('pa.status', 'rejected')->count();

        $summary = [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];

        $query = DB::table('users as u')
            ->join('instructor_profiles as ip', 'ip.user_id', '=', 'u.id')
            ->leftJoinSub($latestPayoutQuery, 'latest_pa', function ($join): void {
                $join->on('latest_pa.user_id', '=', 'u.id');
            })
            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id');

        if (!empty($queryParams['search'])) {
            $search = trim((string) $queryParams['search']);
            $query->where(function ($builder) use ($search): void {
                $builder->where('u.full_name', 'like', "%{$search}%")
                        ->orWhere('u.email', 'like', "%{$search}%")
                        ->orWhere('u.phone', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['status'])) {
            $st = $queryParams['status'];
            if ($st === 'pending') {
                $query->where('pa.status', 'pending_verification');
            } elseif ($st === 'approved') {
                $query->where('u.role', 'instructor')->where('pa.status', 'active');
            } elseif ($st === 'rejected') {
                $query->where('pa.status', 'rejected');
            }
        }

        if (!empty($queryParams['expertise'])) {
            $query->where('ip.expertise', $queryParams['expertise']);
        }

        if (!empty($queryParams['experience_range'])) {
            $range = $queryParams['experience_range'];
            if ($range === 'under_1') {
                $query->where('ip.experience_years', '<', 1);
            } elseif ($range === '1_2') {
                $query->whereBetween('ip.experience_years', [1, 2]);
            } elseif ($range === '3_5') {
                $query->whereBetween('ip.experience_years', [3, 5]);
            } elseif ($range === 'over_5') {
                $query->where('ip.experience_years', '>', 5);
            }
        }

        if (!empty($queryParams['payout_filter'])) {
            $pf = $queryParams['payout_filter'];
            if ($pf === 'linked') {
                $query->whereNotNull('pa.id');
            } elseif ($pf === 'unlinked') {
                $query->whereNull('pa.id');
            } elseif ($pf === 'active') {
                $query->where('pa.status', 'active');
            } elseif ($pf === 'pending_verification') {
                $query->where('pa.status', 'pending_verification');
            }
        }

        $dateFrom = $queryParams['date_from'] ?? null;
        $dateTo = $queryParams['date_to'] ?? null;
        if ($dateFrom) {
            $query->whereDate('ip.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('ip.created_at', '<=', $dateTo);
        }

        $sortBy = $queryParams['sort_by'] ?? 'newest';
        
        if ($sortBy === 'newest') {
            $query->orderByDesc('ip.created_at');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('ip.created_at');
        } elseif ($sortBy === 'reviewed_newest') {
            $query->orderByDesc('pa.connected_at')->orderByDesc('pa.updated_at');
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('u.full_name');
        } elseif ($sortBy === 'name_desc') {
            $query->orderByDesc('u.full_name');
        } elseif ($sortBy === 'experience_asc') {
            $query->orderBy('ip.experience_years');
        } elseif ($sortBy === 'experience_desc') {
            $query->orderByDesc('ip.experience_years');
        } elseif ($sortBy === 'specialty_asc') {
            $query->orderBy('ip.expertise');
        } elseif ($sortBy === 'specialty_desc') {
            $query->orderByDesc('ip.expertise');
        } else {
            $query->orderByDesc('ip.created_at');
        }

        $query->orderByDesc('u.id');

        $perPage = min((int) ($queryParams['per_page'] ?? 15), 100);
        $paginator = $query->select([
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
            ->paginate($perPage)
            ->appends($queryParams);

        $mappedItems = collect($paginator->items())->map(function ($row) {
            $status = 'unknown';
            if ($row->role === 'instructor' && $row->payout_status === 'active') {
                $status = 'approved';
            } elseif ($row->payout_status === 'pending_verification') {
                $status = 'pending';
            } elseif ($row->payout_status === 'rejected') {
                $status = 'rejected';
            }

            return [
                'application_status' => $status,
                'submitted_at' => $row->submitted_at ? \Carbon\Carbon::parse($row->submitted_at)->toISOString() : null,
                'review_note' => $status === 'approved' ? 'Tài khoản đã được nâng cấp thành giảng viên.' : ($status === 'rejected' ? 'Yêu cầu đã bị từ chối.' : 'Yêu cầu đang chờ admin duyệt.'),

                'user' => [
                    'id' => $row->user_id,
                    'full_name' => $row->full_name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'role' => $row->role,
                    'status' => $row->user_status,
                    'email_verified_at' => $row->email_verified_at,
                ],

                'instructor_profile' => [
                    'bio' => $row->bio,
                    'expertise' => $row->expertise,
                    'experience_years' => $row->experience_years,
                    'level' => $row->level,
                ],

                'payout_account' => $row->payout_id ? [
                    'id' => $row->payout_id,
                    'provider' => $row->provider,
                    'account_number_masked' => $row->account_number ? (substr($row->account_number, 0, 3) . '******' . substr($row->account_number, -2)) : null,
                    'account_name' => $row->account_name,
                    'status' => $row->payout_status,
                    'connected_at' => $row->connected_at,
                ] : null,
            ];
        })->toArray();

        return [
            'summary' => $summary,
            'items' => $mappedItems,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
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

        $pendingPayout = $this->instructorUpgradeRepository->findPendingPayoutByUserId($userId);

        if (! $pendingPayout) {
            throw new BusinessException('Tài khoản chưa có yêu cầu payout đang chờ duyệt.', 400);
        }

        if ($user->role !== 'learner' && $user->role !== 'instructor') {
            throw new BusinessException('Chỉ tài khoản learner hoặc instructor mới có thể phê duyệt hồ sơ.', 400);
        }

        if ($user->status !== 'active' || ! $user->email_verified_at) {
            throw new BusinessException('Tài khoản chưa active hoặc chưa xác thực email.', 400);
        }

        $profile = $this->instructorUpgradeRepository->findProfileByUserId($userId);

        if (! $profile) {
            throw new BusinessException('Tài khoản chưa có hồ sơ giảng viên.', 400);
        }

        DB::transaction(function () use ($user, $pendingPayout) {
            if ($user->role === 'learner') {
                $this->instructorUpgradeRepository->updateUser($user, [
                    'role' => 'instructor',
                ]);
            }

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

        if ($user->role !== 'learner' && $user->role !== 'instructor') {
            throw new BusinessException('Chỉ có thể từ chối yêu cầu của tài khoản learner hoặc instructor.', 400);
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
