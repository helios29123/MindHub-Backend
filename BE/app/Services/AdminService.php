<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function getBanners(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams["per_page"] ?? 10), 100);
        return Banner::orderBy("sort_order")
            ->orderByDesc("id")
            ->paginate($perPage);
    }

    public function getBanner(int $id): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        return $banner;
    }

    public function createBanner(array $data): Banner
    {
        return Banner::create($data);
    }

    public function updateBanner(int $id, array $data): Banner
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        $banner->update($data);
        return $banner;
    }

    public function deleteBanner(int $id): void
    {
        $banner = Banner::find($id);

        if (!$banner) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        $banner->delete();
    }
    public function getUsers(array $queryParams): LengthAwarePaginator
    {
        $perPage = min((int) ($queryParams["per_page"] ?? 10), 100);

        $query = User::query();

        if (!empty($queryParams["search"])) {
            $search = trim((string) $queryParams["search"]);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where("full_name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%")
                    ->orWhere("phone", "like", "%{$search}%");
            });
        }

        if (!empty($queryParams["role"])) {
            $query->where("role", $queryParams["role"]);
        }

        if (!empty($queryParams["status"])) {
            $query->where("status", $queryParams["status"]);
        }

        $sortBy = $queryParams["sort_by"] ?? "id";
        $sortDirection = $queryParams["sort_direction"] ?? "desc";

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->appends($queryParams);
    }

    public function getUser(int $id): User
    {
        $user = User::find($id);

        if (!$user) {
            throw new BusinessException("Không tìm thấy dữ liệu.", 404);
        }

        return $user;
    }

    public function createUser(array $data): User
    {
        $data["password_hash"] = Hash::make($data["password"]);
        unset($data["password"]);

        $data["oauth_account_login"] = false;

        if (($data["status"] ?? null) === User::STATUS_LOCKED) {
            $data["locked"] = true;
        } else {
            $data["locked"] = false;
            $data["locked_reason"] = null;
        }

        return User::create($data);
    }

    public function updateUser(
        int $id,
        array $data,
        ?int $currentAdminId = null,
    ): User {
        $user = $this->getUser($id);

        if (
            $currentAdminId !== null &&
            (int) $user->id === (int) $currentAdminId
        ) {
            if (isset($data["role"]) && $data["role"] !== $user->role) {
                throw new BusinessException(
                    "Không thể thay đổi role của chính mình.",
                    400,
                );
            }

            if (
                isset($data["status"]) &&
                $data["status"] !== User::STATUS_ACTIVE
            ) {
                throw new BusinessException(
                    "Không thể tự khóa hoặc vô hiệu hóa tài khoản của chính mình.",
                    400,
                );
            }
        }

        if (isset($data["password"])) {
            $data["password_hash"] = Hash::make($data["password"]);
            unset($data["password"]);
        }

        if (isset($data["status"])) {
            if ($data["status"] === User::STATUS_LOCKED) {
                $data["locked"] = true;
            } else {
                $data["locked"] = false;
                $data["locked_reason"] = null;
            }
        }

        $user->update($data);

        return $user->refresh();
    }

    public function deleteUser(int $id, ?int $currentAdminId = null): void
    {
        $user = $this->getUser($id);

        if (
            $currentAdminId !== null &&
            (int) $user->id === (int) $currentAdminId
        ) {
            throw new BusinessException(
                "Không thể xóa tài khoản của chính mình.",
                400,
            );
        }

        DB::transaction(function () use ($user): void {
            $user->delete();

            DB::table("sessions")
                ->where("user_id", $user->id)
                ->whereNull("revoked_at")
                ->update([
                    "revoked_at" => now(),
                ]);
        });
    }
}
