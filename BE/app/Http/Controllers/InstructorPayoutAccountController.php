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
        $rawStatus = $request->validated()['status'] ?? 'verified';
        $status = match ($rawStatus) {
            'active' => PayoutAccount::STATUS_VERIFIED,
            'inactive' => PayoutAccount::STATUS_DISABLED,
            default => $rawStatus,
        };

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
            ->where('status', PayoutAccount::STATUS_VERIFIED)
            ->where('is_default', true)
            ->first();

        if (!$account) {
            $account = PayoutAccount::where('user_id', $instructorId)
                ->where('status', PayoutAccount::STATUS_VERIFIED)
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

        $hasVerified = PayoutAccount::where('user_id', $instructorId)
            ->where('status', PayoutAccount::STATUS_VERIFIED)
            ->exists();

        $account = PayoutAccount::create([
            'user_id' => $instructorId,
            'provider' => $request->input('provider'),
            'account_number' => $request->input('account_number'),
            'account_name' => $request->input('account_name'),
            'status' => PayoutAccount::STATUS_VERIFIED,
            'verified_at' => now(),
            'is_default' => !$hasVerified,
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
            ->where('status', PayoutAccount::STATUS_VERIFIED)
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

        $account->status = PayoutAccount::STATUS_DISABLED;
        $account->disabled_at = now();
        $account->is_default = false;
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

    public function sendChangeOtp(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $instructorId = (int) $user->id;

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'provider' => 'nullable|string|max:50',
            'bank_code' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'required|string|min:4|max:50',
            'account_name' => 'nullable|string|max:100',
            'account_holder_name' => 'nullable|string|max:100',
            'branch_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Thông tin tài khoản nhận tiền không hợp lệ.', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();
        $accountName = $validated['account_holder_name'] ?? ($validated['account_name'] ?? '');
        if (trim($accountName) === '') {
            return ApiResponse::error('Tên chủ tài khoản là bắt buộc.', ['account_holder_name' => ['Tên chủ tài khoản là bắt buộc.']], 422);
        }

        $provider = !empty($validated['bank_name']) 
            ? $validated['bank_name'] . (!empty($validated['bank_code']) ? ' (' . $validated['bank_code'] . ')' : '')
            : ($validated['provider'] ?? 'bank');

        // Check if updating an existing account owned by instructor
        if ($id > 0) {
            $existing = PayoutAccount::where('id', $id)
                ->where('user_id', $instructorId)
                ->first();

            if (!$existing) {
                return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
            }

            if ($existing->account_number === $validated['account_number'] && $existing->account_name === $accountName) {
                return ApiResponse::error('Số tài khoản mới trùng với tài khoản hiện tại.', [], 422);
            }
        }

        // Rate limit resend (60s)
        $cacheKeyPending = "payout_change_pending_" . $instructorId;
        $cacheKeyPayload = "payout_change_payload_" . $instructorId;

        if (\Illuminate\Support\Facades\Cache::has($cacheKeyPending)) {
            return ApiResponse::error('Bạn đã yêu cầu gửi mã OTP gần đây. Vui lòng chờ 60 giây trước khi thử lại.', [], 429);
        }

        // Save pending change payload in Cache for 10 minutes
        $payload = [
            'account_id' => $id,
            'provider' => $provider,
            'account_number' => trim($validated['account_number']),
            'account_name' => mb_strtoupper(trim($accountName)),
            'bank_code' => $validated['bank_code'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'branch_name' => $validated['branch_name'] ?? null,
        ];

        \Illuminate\Support\Facades\Cache::put($cacheKeyPayload, $payload, now()->addMinutes(10));
        \Illuminate\Support\Facades\Cache::put($cacheKeyPending, true, now()->addSeconds(60));

        // Generate 6-digit OTP using UserOtp
        $otp = app(\App\Services\Auth\OtpService::class)->generate((int) $instructorId, 'payout_account_change', 300);

        // Send email notification (Log in dev environment)
        try {
            \Illuminate\Support\Facades\Mail::raw("Mã OTP xác thực thay đổi tài khoản nhận tiền MindHub của bạn là: {$otp}. Mã có hiệu lực trong 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('[MindHub] Mã OTP xác thực thay đổi tài khoản nhận tiền');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info("Payout OTP for user {$user->id} ({$user->email}): {$otp}");
        }

        $emailParts = explode('@', $user->email);
        $maskedEmail = substr($emailParts[0], 0, 2) . '****@' . ($emailParts[1] ?? 'mindhub.test');

        return ApiResponse::success([
            'masked_email' => $maskedEmail,
            'expires_in' => 300,
            'resend_after' => 60,
        ], 'Mã OTP đã được gửi đến email của bạn.');
    }

    public function verifyChange(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $instructorId = (int) $user->id;

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $otpCode = trim($request->input('otp'));

        // 1. Verify OTP using UserOtp
        try {
            app(\App\Services\Auth\OtpService::class)->verify((int) $instructorId, 'payout_account_change', (string) $otpCode);
        } catch (\App\Exceptions\BusinessException $e) {
            return ApiResponse::error($e->getMessage(), [], $e->getCode());
        } catch (\Throwable $e) {
            return ApiResponse::error('Mã OTP không chính xác hoặc đã hết hạn.', [], 422);
        }

        // 2. Fetch pending payload
        $cacheKeyPayload = "payout_change_payload_" . $instructorId;
        $payload = \Illuminate\Support\Facades\Cache::get($cacheKeyPayload);

        if (!$payload) {
            return ApiResponse::error('Yêu cầu thay đổi tài khoản đã hết hạn. Vui lòng thực hiện lại từ đầu.', [], 409);
        }

        // 3. Apply changes in DB transaction
        $account = DB::transaction(function () use ($instructorId, $id, $payload) {
            PayoutAccount::where('user_id', $instructorId)->update(['is_default' => false]);

            if ($id > 0) {
                $existing = PayoutAccount::where('id', $id)
                    ->where('user_id', $instructorId)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'provider' => $payload['provider'],
                        'account_number' => $payload['account_number'],
                        'account_name' => $payload['account_name'],
                        'status' => PayoutAccount::STATUS_VERIFIED,
                        'is_default' => true,
                        'verified_at' => now(),
                        'disabled_at' => null,
                    ]);
                    return $existing;
                }
            }

            // Create new verified default payout account
            return PayoutAccount::create([
                'user_id' => $instructorId,
                'provider' => $payload['provider'],
                'account_number' => $payload['account_number'],
                'account_name' => $payload['account_name'],
                'status' => PayoutAccount::STATUS_VERIFIED,
                'verified_at' => now(),
                'is_default' => true,
            ]);
        });

        // Clear pending payload
        \Illuminate\Support\Facades\Cache::forget($cacheKeyPayload);
        \Illuminate\Support\Facades\Cache::forget("payout_change_pending_" . $instructorId);

        // Send confirmation email
        try {
            \Illuminate\Support\Facades\Mail::raw("Tài khoản nhận tiền của bạn tại MindHub vừa được cập nhật thành công thành: {$account->account_name} ({$payload['provider']}). Nếu bạn không thực hiện thao tác này, vui lòng liên hệ bộ phận hỗ trợ ngay lập tức.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('[MindHub] Cảnh báo an toàn: Thay đổi tài khoản nhận tiền thành công');
            });
        } catch (\Throwable $e) {
            // Ignore
        }

        return ApiResponse::success(
            new InstructorPayoutAccountResource($account),
            'Cập nhật tài khoản nhận tiền thành công.'
        );
    }

    public function reveal(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $instructorId = (int) $user->id;

        $account = PayoutAccount::where('id', $id)
            ->where('user_id', $instructorId)
            ->first();

        if (!$account) {
            return ApiResponse::error('Không tìm thấy tài khoản nhận tiền.', [], 404);
        }

        if ($account->status !== 'active') {
            return ApiResponse::error('Tài khoản nhận tiền đang bị vô hiệu hóa hoặc chưa xác minh.', [], 409);
        }

        $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu xác nhận.',
        ]);

        $passwordHash = $user->getAuthPassword() ?: ($user->password_hash ?? $user->password);
        if (!$passwordHash || !\Illuminate\Support\Facades\Hash::check($request->input('password'), $passwordHash)) {
            return ApiResponse::error('Mật khẩu xác nhận không chính xác.', ['password' => ['Mật khẩu xác nhận không chính xác.']], 422);
        }

        return ApiResponse::success([
            'account_number' => $account->account_number,
            'expires_in' => 30,
        ], 'Xác thực thành công.');
    }
}
