<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instructor\InstructorPayoutAccountIndexRequest;
use App\Http\Resources\Instructor\InstructorPayoutAccountResource;
use App\Models\PayoutAccount;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class InstructorPayoutAccountController extends Controller
{
    public function index(InstructorPayoutAccountIndexRequest $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $status = $request->validated()['status'] ?? 'active';

        $accounts = PayoutAccount::where('user_id', $instructorId)
            ->where('status', $status)
            ->get();

        return ApiResponse::success(
            InstructorPayoutAccountResource::collection($accounts)->resolve($request),
            'Lấy danh sách tài khoản nhận tiền thành công.'
        );
    }

    public function default(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;

        $account = PayoutAccount::where('user_id', $instructorId)
            ->where('status', 'active')
            ->where('is_default', true)
            ->first();

        if (!$account) {
            $account = PayoutAccount::where('user_id', $instructorId)
                ->where('status', 'active')
                ->first();
        }

        if (!$account) {
            return ApiResponse::success(null, 'Không có tài khoản nhận tiền mặc định.');
        }

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Lấy tài khoản nhận tiền mặc định thành công.'
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
        }

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Lấy chi tiết tài khoản nhận tiền thành công.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $instructorId = (int) $request->user()->id;

        $request->validate([
            'provider' => 'required|string|in:bank,momo,zalopay',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ]);

        $hasActive = PayoutAccount::where('user_id', $instructorId)
            ->where('status', 'active')
            ->exists();

        $account = PayoutAccount::create([
            'user_id' => $instructorId,
            'provider' => $request->input('provider'),
            'account_number' => $request->input('account_number'),
            'account_name' => $request->input('account_name'),
            'status' => 'active',
            'connected_at' => now(),
            'is_default' => !$hasActive,
        ]);

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Tạo tài khoản nhận tiền thành công.',
            201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
        }

        $request->validate([
            'provider' => 'sometimes|required|string|in:bank,momo,zalopay',
            'account_number' => 'sometimes|required|string|max:50',
            'account_name' => 'sometimes|required|string|max:100',
        ]);

        $account->update($request->only(['provider', 'account_number', 'account_name']));

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Cập nhật tài khoản nhận tiền thành công.'
        );
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->where('status', 'active')
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền hợp lệ.', [], 404);
        }

        PayoutAccount::where('user_id', $instructorId)->update(['is_default' => false]);

        $account->is_default = true;
        $account->save();

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Đặt làm tài khoản mặc định thành công.'
        );
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
        }

        $account->status = 'inactive';
        $account->disabled_at = now();
        $account->save();

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Vô hiệu hóa tài khoản nhận tiền thành công.'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $instructorId = (int) $request->user()->id;
        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
        }

        $hasPending = DB::table('withdraw_requests')
            ->where('user_id', $instructorId)
            ->where('payout_account_id', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasPending) {
            return ApiResponse::error('Không thể xóa tài khoản nhận tiền đang có yêu cầu rút tiền chờ xử lý.', [], 422);
        }

        $account->delete();

        return ApiResponse::success(null, 'Xóa tài khoản nhận tiền thành công.');
    }
}
