<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $token
    ) {}

    public function build(): self
    {
        $name = e($this->user->full_name);
        $resetUrl = e(config('app.frontend_url', 'http://localhost:5173') . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->user->email));

        $otpCode = e($this->token);
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; }
                .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
                .logo { font-size: 24px; font-weight: 900; color: #0066FF; letter-spacing: -0.5px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .otp-box { text-align: center; margin: 24px 0; background: #f8fafc; border: 2px dashed #0066FF; border-radius: 12px; padding: 16px; }
                .otp-code { font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 900; letter-spacing: 8px; color: #0066FF; }
                .btn-wrapper { text-align: center; margin: 20px 0; }
                .btn { display: inline-block; background-color: #0066FF; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(0,102,255,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
                .link-box { word-break: break-all; background: #f8fafc; padding: 12px; border-radius: 8px; font-size: 12px; color: #64748b; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>MindHub</div>
                </div>
                <div class='content'>
                    <h2>Xin chào <strong>{$name}</strong>,</h2>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản <strong>MindHub</strong> của mình.</p>
                    <p>Mã OTP khôi phục mật khẩu gồm 6 chữ số của bạn là:</p>
                    <div class='otp-box'>
                        <span class='otp-code'>{$otpCode}</span>
                    </div>
                    <p>Hoặc nhấp vào nút bên dưới để tự động điền mã và đặt lại mật khẩu mới ngay:</p>
                    <div class='btn-wrapper'>
                        <a href='{$resetUrl}' class='btn' target='_blank'>Đặt lại mật khẩu ngay</a>
                    </div>
                    <p style='font-size: 13px; color: #64748b;'>Liên kết khôi phục trực tiếp:</p>
                    <div class='link-box'>{$resetUrl}</div>
                    <p style='font-size: 13px; color: #64748b; margin-top: 16px;'>Mã OTP này có hiệu lực trong vòng <strong>60 phút</strong>. Nếu bạn không yêu cầu đặt lại mật khẩu, xin hãy bỏ qua email này.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " MindHub. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this
            ->subject('[MindHub] Yêu cầu đặt lại mật khẩu tài khoản')
            ->html($html);
    }
}
