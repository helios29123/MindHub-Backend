<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleTokenVerifier
{
    public function verify(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            throw new BusinessException('Chưa cấu hình GOOGLE_CLIENT_ID trong file .env.', 500);
        }

        try {
            $response = Http::timeout(15)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);
        } catch (Throwable $e) {
            throw new BusinessException('Không thể kết nối tới Google để xác thực token.', 500);
        }

        if (!$response->successful()) {
            throw new BusinessException('Google token không hợp lệ hoặc đã hết hạn.', 401);
        }

        $payload = $response->json();

        if (($payload['aud'] ?? null) !== $clientId) {
            throw new BusinessException('Google token không đúng GOOGLE_CLIENT_ID.', 401);
        }

        if (empty($payload['sub']) || empty($payload['email'])) {
            throw new BusinessException('Google token thiếu thông tin tài khoản.', 401);
        }

        return [
            'provider' => 'google',
            'provider_id' => $payload['sub'],
            'email' => $payload['email'],
            'full_name' => $payload['name'] ?? $payload['email'],
            'avatar' => $payload['picture'] ?? null,
            'email_verified' => ($payload['email_verified'] ?? false) === true
                || ($payload['email_verified'] ?? false) === 'true',
        ];
    }
}
