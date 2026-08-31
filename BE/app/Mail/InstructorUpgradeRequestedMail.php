<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstructorUpgradeRequestedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $applicant,
        public readonly array $applicationData
    ) {
    }

    public function build(): self
    {
        $applicantName = e($this->applicant->full_name);
        $applicantEmail = e($this->applicant->email);
        $phone = e($this->applicationData['phone'] ?? $this->applicant->phone ?? 'Chưa cập nhật');
        $expertise = e($this->applicationData['expertise'] ?? 'Chưa cập nhật');
        $experienceYears = e((string)($this->applicationData['experience_years'] ?? '0'));
        $bio = e($this->applicationData['bio'] ?? 'Chưa cung cấp giới thiệu');
        $bankName = e($this->applicationData['bank_provider'] ?? 'Techcombank');
        $accountNumber = e($this->applicationData['bank_account_number'] ?? 'Chưa cập nhật');
        $accountName = e($this->applicationData['bank_account_name'] ?? 'Chưa cập nhật');

        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $adminReviewUrl = "{$frontendUrl}/admin/instructors/upgrade-requests";

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject("[MindHub Admin] Yêu cầu đăng ký làm Giảng viên từ: {$applicantName}")
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
                .badge { display: inline-block; background-color: #fef3c7; color: #d97706; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .title { font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px; }
                .info-item { font-size: 13px; color: #64748b; margin-bottom: 6px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #0066FF; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(0,102,255,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>👨‍🏫 Đăng ký Giảng viên mới</div>
                </div>
                <div class='content'>
                    <h2>Kính gửi Quản trị viên,</h2>
                    <p>Học viên <strong>{$applicantName}</strong> vừa nộp đơn đăng ký trở thành Giảng viên trên nền tảng <strong>MindHub</strong>.</p>
                    
                    <div class='card'>
                        <div class='title'>Hồ sơ đăng ký Giảng viên</div>
                        <div class='info-item'>👤 Họ tên: <strong>{$applicantName}</strong></div>
                        <div class='info-item'>✉️ Email: <strong>{$applicantEmail}</strong></div>
                        <div class='info-item'>📞 Số điện thoại: <strong>{$phone}</strong></div>
                        <div class='info-item'>💡 Chuyên môn: <strong>{$expertise}</strong></div>
                        <div class='info-item'>⏳ Kinh nghiệm: <strong>{$experienceYears} năm</strong></div>
                        <div class='info-item'>🏦 Ngân hàng: <strong>{$bankName}</strong> ({$accountNumber} - {$accountName})</div>
                        <div class='info-item' style='margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1;'>
                            📝 <em>Giới thiệu: {$bio}</em>
                        </div>
                    </div>

                    <p>Vui lòng xem xét hồ sơ năng lực và duyệt tài khoản để người dùng có thể bắt đầu xuất bản khóa học:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$adminReviewUrl}' class='btn' target='_blank'>Xem & Phê Duyệt Hồ Sơ Giảng Viên</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>Email này được tự động gửi từ hệ thống quản trị MindHub E-Learning Platform.</p>
                    <p>&copy; " . date('Y') . " MindHub Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ")
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($logoFile, $cidName) {
                if (file_exists($logoFile)) {
                    $message->embedFromPath($logoFile, $cidName);
                }
            });
    }
}
