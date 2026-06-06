<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AccessTokenService
{
    private const ACCESS_TOKEN_EXPIRES_MINUTES = 60;
    private const REFRESH_TOKEN_EXPIRES_DAYS = 30;

    public function createAccessToken(int $userId, int $sessionId): array
    {
        $expiresAt = now()->addMinutes(self::ACCESS_TOKEN_EXPIRES_MINUTES);

        $payload = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt->timestamp,
            'issued_at' => now()->timestamp,
        ];

        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedPayload, $this->getSigningKey());

        return [
            'token' => $encodedPayload . '.' . $signature,
            'expires_at' => $expiresAt,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_MINUTES * 60,
        ];
    }

    public function createRefreshToken(): array
    {
        $plainRefreshToken = Str::random(80);

        return [
            'token' => $plainRefreshToken,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_EXPIRES_DAYS),
        ];
    }

    public function parseAccessToken(string $plainAccessToken): array
    {
        $tokenParts = explode('.', $plainAccessToken);

        if (count($tokenParts) !== 2) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        [$encodedPayload, $signature] = $tokenParts;
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->getSigningKey());

        if (! hash_equals($expectedSignature, $signature)) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        $userId = (int) Arr::get($payload, 'user_id');
        $sessionId = (int) Arr::get($payload, 'session_id');
        $expiresAt = (int) Arr::get($payload, 'expires_at');

        if ($userId <= 0 || $sessionId <= 0 || $expiresAt <= 0) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        if (now()->timestamp > $expiresAt) {
            throw new BusinessException('Token đã hết hạn.', 401);
        }

        return [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
        ];
    }

    private function getSigningKey(): string
    {
        $appKey = (string) config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $decodedKey = base64_decode(substr($appKey, 7), true);

            return $decodedKey !== false ? $decodedKey : $appKey;
        }

        return $appKey;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decodedValue = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decodedValue === false) {
            throw new BusinessException('Token không hợp lệ.', 401);
        }

        return $decodedValue;
    }
}
