<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verifyUrl,
        public readonly ?string $otpCode = null
    ) {
    }

    public function build(): self
    {
        $name = e($this->user->full_name);
        $url = e($this->verifyUrl);
        $otpDisplay = $this->otpCode ? e($this->otpCode) : null;
        $phoneDisplay = $this->user->phone ? e($this->user->phone) : null;

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        $otpBlock = $otpDisplay ? "
            <div style='background: #f0fdf4; border: 2px dashed #059669; border-radius: 14px; padding: 20px; text-align: center; margin: 20px 0;'>
                <p style='margin: 0; font-size: 13px; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;'>MÃ OTP XÁC THỰC TÀI KHOẢN" . ($phoneDisplay ? " & SỐ ĐIỆN THOẠI ({$phoneDisplay})" : "") . ":</p>
                <div style='font-size: 34px; font-weight: 900; letter-spacing: 8px; color: #059669; margin-top: 8px;'>{$otpDisplay}</div>
            </div>
        " : "";

        $instructionText = $otpDisplay
            ? "Vui lòng nhập mã OTP <strong>{$otpDisplay}</strong> ở trên hoặc bấm nút bên dưới để xác thực tài khoản của bạn:"
            : "Vui lòng bấm nút bên dưới để xác thực địa chỉ email của bạn:";

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; }
                .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
                .logo-img { height: 68px; max-width: 300px; object-fit: contain; margin: 0 auto 8px auto; display: block; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #0066FF; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(0,102,255,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
                .link-box { word-break: break-all; background: #f8fafc; padding: 12px; border-radius: 8px; font-size: 12px; color: #64748b; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                </div>
                <div class='content'>
                    <h2>Xin chào <strong>{$name}</strong>,</h2>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại hệ thống học trực tuyến <strong>MindHub</strong>.</p>
                    {$otpBlock}
                    <p>{$instructionText}</p>
                    <div class='btn-wrapper'>
                        <a href='{$url}' class='btn' target='_blank'>Xác thực tài khoản ngay</a>
                    </div>
                    <p style='font-size: 13px; color: #64748b;'>Nếu nút bấm trên không hoạt động, bạn vui lòng copy đường dẫn sau dán vào trình duyệt:</p>
                    <div class='link-box'>{$url}</div>
                    <p style='font-size: 13px; color: #64748b; margin-top: 16px;'>Mã xác thực này có hiệu lực trong vòng <strong>60 phút</strong>.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " MindHub. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this
            ->subject('[MindHub] Mã OTP & Link xác thực tài khoản MindHub')
            ->html($html)
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($logoFile, $cidName) {
                if (file_exists($logoFile)) {
                    $message->embedFromPath($logoFile, $cidName);
                }
            });
    }
}
