<?php

namespace App\Models;

use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserOtp extends Model
{
    use HasFactory;

    protected $table = 'user_otps';

    protected $fillable = [
        'user_id',
        'purpose',
        'code_hash',
        'expires_at',
        'used_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new 6-digit OTP code for user and purpose.
     */
    public static function generateOtp(
        int $userId,
        string $purpose = 'change_password',
        int $expirySeconds = 300
    ): string {
        // Invalidate any existing unused OTPs for this purpose
        static::query()
            ->where('user_id', $userId)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        // Generate 6-digit numeric string
        $code = sprintf('%06d', random_int(0, 999999));

        static::create([
            'user_id' => $userId,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addSeconds($expirySeconds),
            'used_at' => null,
            'attempts' => 0,
        ]);

        return $code;
    }

    /**
     * Verify and consume OTP code.
     */
    public static function verifyOtp(
        int $userId,
        string $code,
        string $purpose = 'change_password'
    ): bool {
        $cleanCode = trim($code);
        if (strlen($cleanCode) !== 6 || !ctype_digit($cleanCode)) {
            throw new BusinessException('Mã OTP phải có đúng 6 chữ số.', 422);
        }

        $otpRecord = static::query()
            ->where('user_id', $userId)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            throw new BusinessException('Mã OTP không chính xác hoặc đã hết hạn.', 422);
        }

        if ($otpRecord->attempts >= 5) {
            $otpRecord->update(['used_at' => now()]);
            throw new BusinessException('Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng yêu cầu mã OTP mới.', 429);
        }

        $otpRecord->increment('attempts');

        if (!Hash::check($cleanCode, $otpRecord->code_hash)) {
            $remaining = 5 - $otpRecord->attempts;
            if ($remaining <= 0) {
                $otpRecord->update(['used_at' => now()]);
                throw new BusinessException('Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng yêu cầu mã OTP mới.', 429);
            }
            throw new BusinessException("Mã OTP không chính xác. Bạn còn {$remaining} lần thử.", 422);
        }

        $otpRecord->update(['used_at' => now()]);

        return true;
    }
}
