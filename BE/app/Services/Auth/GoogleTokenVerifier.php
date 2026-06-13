<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use Google\Client as GoogleClient;
use Exception;

class GoogleTokenVerifier
{
    public function verify(string $googleToken): array
    {
        // For local development and testing, support mocked tokens
        if (app()->environment('testing', 'local') && str_starts_with($googleToken, 'mock_')) {
            $rawToken = substr($googleToken, 5);
            $decodedJson = base64_decode($rawToken, true);

            if ($decodedJson !== false) {
                $payload = json_decode($decodedJson, true);

                if (is_array($payload)) {
                    $providerId = $payload['provider_id'] ?? null;
                    $email = $payload['email'] ?? null;
                    $fullName = $payload['full_name'] ?? null;

                    if (is_string($providerId) && $providerId !== '' && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return [
                            'provider' => 'google',
                            'provider_id' => $providerId,
                            'email' => $email,
                            'full_name' => is_string($fullName) && $fullName !== '' ? $fullName : 'Google User',
                        ];
                    }
                }
            }
        }

        try {
            $clientId = config('services.google.client_id');
            $client = new GoogleClient(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($googleToken);

            if ($payload) {
                $providerId = $payload['sub'] ?? null;
                $email = $payload['email'] ?? null;
                $fullName = $payload['name'] ?? null;

                if (is_string($providerId) && $providerId !== '' && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'provider' => 'google',
                        'provider_id' => $providerId,
                        'email' => $email,
                        'full_name' => is_string($fullName) && $fullName !== '' ? $fullName : 'Google User',
                    ];
                }
            }
        } catch (Exception $exception) {
            throw new BusinessException('Google token không hợp lệ.', 401);
        }

        throw new BusinessException('Google token không hợp lệ.', 401);
    }
}
