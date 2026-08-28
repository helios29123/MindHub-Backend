<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class OtpService
{
    public function generate(int $userId, string $purpose, int $ttlSeconds = 300): string
    {
        return DB::transaction(function () use ($userId, $purpose, $ttlSeconds): string {
            UserOtp::query()
                ->where('user_id', $userId)
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            UserOtp::query()->create([
                'user_id' => $userId,
                'purpose' => $purpose,
                'code_hash' => Hash::make($plainCode),
                'expires_at' => now()->addSeconds(max(60, $ttlSeconds)),
                'used_at' => null,
                'attempts' => 0,
            ]);

            return $plainCode;
        });
    }

    public function verify(
        int $userId,
        string $purpose,
        string $plainCode,
        int $maxAttempts = 5
    ): UserOtp {
        $result = DB::transaction(function () use ($userId, $purpose, $plainCode, $maxAttempts): array {
            /** @var UserOtp|null $otp */
            $otp = UserOtp::query()
                ->where('user_id', $userId)
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                return ['otp' => null, 'error' => 'Mã OTP không tồn tại hoặc đã được sử dụng.'];
            }

            if ($otp->expires_at === null || $otp->expires_at->isPast()) {
                return ['otp' => null, 'error' => 'Mã OTP đã hết hạn.'];
            }

            if ((int) $otp->attempts >= $maxAttempts) {
                return ['otp' => null, 'error' => 'Mã OTP đã vượt quá số lần thử cho phép.'];
            }

            if (! Hash::check($plainCode, (string) $otp->code_hash)) {
                $otp->forceFill([
                    'attempts' => ((int) $otp->attempts) + 1,
                ])->save();

                return ['otp' => null, 'error' => 'Mã OTP không chính xác.'];
            }

            $otp->forceFill(['used_at' => now()])->save();

            return ['otp' => $otp->refresh(), 'error' => null];
        });

        if ($result['error'] !== null) {
            throw new BusinessException((string) $result['error'], 422);
        }

        /** @var UserOtp $otp */
        $otp = $result['otp'];

        return $otp;
    }
}
