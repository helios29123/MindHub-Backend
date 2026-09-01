<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpeedSmsService
{
    private string $accessToken;
    private string $baseUrl = 'https://api.speedsms.vn/index.php';

    public function __construct()
    {
        $this->accessToken = (string) config('services.speedsms.access_token', env('SPEEDSMS_ACCESS_TOKEN', ''));
    }

    /**
     * Gửi mã OTP xác thực qua tin nhắn SMS tới số điện thoại
     *
     * @param string $phoneNumber Số điện thoại nhận (VD: 0376630401)
     * @param string $otpCode Mã OTP 6 chữ số
     * @return array Kết quả trả về từ SpeedSMS API
     */
    public function sendOtp(string $phoneNumber, string $otpCode): array
    {
        $phoneNumber = trim($phoneNumber);
        if (empty($this->accessToken)) {
            Log::warning("SpeedSMS: Chưa cấu hình SPEEDSMS_ACCESS_TOKEN trong .env");
            return [
                'success' => false,
                'message' => 'Chưa cấu hình API Key SpeedSMS',
            ];
        }

        $content = "Ma OTP MindHub cua ban la {$otpCode}. Ma co hieu luc trong 10 phut.";

        try {
            // Gọi SpeedSMS API gửi tin nhắn SMS
            $response = Http::withBasicAuth($this->accessToken, 'x')
                ->timeout(10)
                ->post("{$this->baseUrl}/sms/send", [
                    'to' => [$phoneNumber],
                    'content' => $content,
                    'sms_type' => 2, // CSKH / OTP
                    'sender' => '',
                ]);

            $result = $response->json() ?? [];
            Log::info("SpeedSMS API response for {$phoneNumber}: " . json_encode($result));

            if (isset($result['status']) && $result['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $result['data'] ?? null,
                    'message' => 'Đã gửi tin nhắn SMS thành công.',
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Lỗi từ nhà cung cấp SpeedSMS',
                'raw' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error("SpeedSMS Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
