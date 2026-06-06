<?php

namespace App\Services;

use App\Exceptions\BusinessException;

class GoogleTokenVerifier
{
    public function verify(string $googleToken): array
    {
        $decodedJson = base64_decode($googleToken, true);

        if ($decodedJson === false) {
            throw new BusinessException('Google token không hợp lệ.', 401);
        }

        $payload = json_decode($decodedJson, true);

        if (! is_array($payload)) {
            throw new BusinessException('Google token không hợp lệ.', 401);
        }

        $providerId = $payload['provider_id'] ?? null;
        $email = $payload['email'] ?? null;
        $fullName = $payload['full_name'] ?? null;

        if (! is_string($providerId) || $providerId === '' || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BusinessException('Google token không hợp lệ.', 401);
        }

        return [
            'provider' => 'google',
            'provider_id' => $providerId,
            'email' => $email,
            'full_name' => is_string($fullName) && $fullName !== '' ? $fullName : 'Google User',
        ];
    }
}
