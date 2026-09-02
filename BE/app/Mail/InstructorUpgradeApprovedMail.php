<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstructorUpgradeApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $instructor,
        public readonly ?string $initialPassword = null
    ) {
    }

    public function build(): self
    {
        $instructorName = e($this->instructor->full_name);
        $instructorEmail = e($this->instructor->email);
        $instructorPhone = e($this->instructor->phone ?: 'Chưa cập nhật');
        $initialPassword = $this->initialPassword ? e($this->initialPassword) : null;

        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $loginUrl = "{$frontendUrl}/login?email=" . urlencode($this->instructor->email);
        $forgotPasswordUrl = "{$frontendUrl}/forgot-password?email=" . urlencode($this->instructor->email);

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        $passwordHtml = $initialPassword ? "
            <div class='info-item' style='margin-top: 10px; padding: 12px 14px; background: #ffffff; border-radius: 10px; border: 1.5px dashed #059669;'>
                <div style='font-size: 12px; color: #047857; font-weight: bold; margin-bottom: 3px;'>🔑 MẬT KHẨU ĐĂNG NHẬP ĐƯỢC CẤP:</div>
                <div style='font-family: Consolas, monospace; font-size: 17px; font-weight: 800; color: #065f46; letter-spacing: 1.5px;'>{$initialPassword}</div>
                <div style='font-size: 11px; color: #64748b; margin-top: 4px;'>* Bạn nên đổi lại mật khẩu này sau lần đăng nhập đầu tiên để bảo mật tài khoản.</div>
            </div>
        " : "
            <div class='info-item' style='margin-top: 10px; padding: 12px 14px; background: #ffffff; border-radius: 10px; border: 1px dashed #94a3b8;'>
                <div style='font-size: 12px; color: #475569; font-weight: bold; margin-bottom: 2px;'>🔑 MẬT KHẨU ĐĂNG NHẬP:</div>
                <div style='font-size: 14px; font-weight: 700; color: #1e293b;'>Mật khẩu bạn đã thiết lập khi đăng ký tài khoản</div>
                <div style='font-size: 12px; color: #64748b; margin-top: 5px;'>
                    <em>Nếu bạn không nhớ mật khẩu, có thể bấm vào <a href='{$forgotPasswordUrl}' style='color: #0284c7; font-weight: 700; text-decoration: underline;' target='_blank'>Quên mật khẩu / Đặt lại mật khẩu</a>.</em>
                </div>
            </div>
        ";

        return $this->subject('[MindHub] Chúc mừng! Hồ sơ Giảng viên của bạn đã được phê duyệt')
            ->html("
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; }
                .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
                .logo-img { height: 68px; max-width: 300px; object-fit: contain; margin: 0 auto 8px auto; display: block; }
                .badge { display: inline-block; background-color: #dcfce7; color: #15803d; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .title { font-size: 17px; font-weight: 800; color: #166534; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid #dcfce7; padding-bottom: 8px; }
                .info-item { font-size: 13px; color: #334155; margin-bottom: 8px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #059669; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(5,150,105,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>🎉 Hồ sơ Giảng viên đã được duyệt</div>
                </div>
                <div class='content'>
                    <h2>Xin chào {$instructorName},</h2>
                    <p>Ban Quản Trị <strong>MindHub</strong> xin trân trọng thông báo: Yêu cầu đăng ký trở thành <strong>Đối tác Giảng viên (Instructor)</strong> của bạn đã chính thức được phê duyệt thành công!</p>
                    
                    <div class='card'>
                        <div class='title'>Thông tin đăng nhập tài khoản Giảng viên</div>
                        <div class='info-item'>👤 Họ và tên: <strong>{$instructorName}</strong></div>
                        <div class='info-item'>✉️ Email đăng nhập: <strong style='color: #059669;'>{$instructorEmail}</strong></div>
                        <div class='info-item'>📱 Số điện thoại đăng nhập: <strong style='color: #059669;'>{$instructorPhone}</strong></div>
                        
                        {$passwordHtml}

                        <div class='info-item' style='margin-top: 12px;'>🌟 Trạng thái: <strong style='color: #16a34a;'>Đã kích hoạt & Sẵn sàng giảng dạy</strong></div>
                        <div class='info-item' style='margin-top: 10px; padding-top: 10px; border-top: 1px dashed #86efac; color: #166534;'>
                            🚀 Bạn có thể sử dụng <strong>Email</strong> hoặc <strong>Số điện thoại</strong> cùng <strong>Mật khẩu</strong> ở trên để đăng nhập trực tiếp vào hệ thống.
                        </div>
                    </div>

                    <p>Bắt đầu hành trình tạo bài giảng và kết nối học viên ngay bây giờ:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$loginUrl}' class='btn' target='_blank'>Đăng Nhập Ngay Vào MindHub</a>
                    </div>

                    <p style='font-size: 13px; color: #64748b;'>Nếu có bất kỳ thắc mắc hoặc cần hỗ trợ trong quá trình tạo khóa học, bạn luôn có thể liên hệ với ban hỗ trợ Giảng viên của MindHub qua email này.</p>
                </div>
                <div class='footer'>
                    <p>Email này được tự động gửi từ hệ thống MindHub E-Learning Platform.</p>
                    <p>&copy; " . date('Y') . " MindHub Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ")
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($logoFile, $cidName) {
                if (file_exists($logoFile)) {
                    $message->embedFromPath($logoFile, $cidName, 'image/jpeg');
                }
            });
    }
}
