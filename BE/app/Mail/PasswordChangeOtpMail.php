<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangeOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $userName
    ) {
    }

    public function build(): self
    {
        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e7e8ed; border-radius: 16px; color: #06091a;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' style='height: 64px; max-width: 280px; object-fit: contain; display: block; margin: 0 auto;' />
                </div>
                <p>Xin chào <strong>{$this->userName}</strong>,</p>
                <p>Bạn đã gửi yêu cầu thay đổi mật khẩu tài khoản giảng viên trên hệ thống MindHub.</p>
                <p>Mã xác minh OTP của bạn là:</p>
                <div style='background: #f0fdf4; border: 2px dashed #007A64; border-radius: 12px; padding: 16px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 32px; font-weight: 900; letter-spacing: 6px; color: #007A64;'>{$this->otpCode}</span>
                </div>
                <p style='font-size: 13px; color: #595959;'>* Mã OTP này có hiệu lực trong vòng <strong>5 phút</strong> và chỉ sử dụng 01 lần.</p>
                <hr style='border: none; border-top: 1px solid #e7e8ed; margin: 24px 0;' />
                <p style='font-size: 12px; color: #737373; margin-bottom: 0;'>⚠️ <strong>Cảnh báo bảo mật:</strong> Tuyệt đối không chia sẻ mã OTP này cho bất kỳ ai. Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ ngay với bộ phận hỗ trợ MindHub để bảo vệ tài khoản.</p>
            </div>
        ";

        return $this->subject('Mã xác minh đổi mật khẩu MindHub')
            ->html($html)
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($logoFile, $cidName) {
                if (file_exists($logoFile)) {
                    $message->embedFromPath($logoFile, $cidName);
                }
            });
    }
}
