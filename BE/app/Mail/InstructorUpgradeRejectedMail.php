<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstructorUpgradeRejectedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $applicant,
        public readonly ?string $reason = null
    ) {
    }

    public function build(): self
    {
        $applicantName = e($this->applicant->full_name);
        $applicantEmail = e($this->applicant->email);
        $applicantPhone = e($this->applicant->phone ?: 'Chưa cập nhật');
        $reasonText = e($this->reason ?: 'Hồ sơ chưa đáp ứng đủ tiêu chuẩn xét duyệt hiện tại của nền tảng.');

        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $resubmitUrl = "{$frontendUrl}/become-instructor?email=" . urlencode($this->applicant->email);

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject('[MindHub] Thông báo về kết quả xét duyệt hồ sơ Giảng viên')
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
                .badge { display: inline-block; background-color: #fee2e2; color: #dc2626; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .title { font-size: 16px; font-weight: 800; color: #991b1b; margin-top: 0; margin-bottom: 8px; }
                .info-item { font-size: 13px; color: #475569; margin-bottom: 6px; }
                .reason-box { background: #ffffff; border-radius: 8px; padding: 14px; border-left: 4px solid #ef4444; font-size: 14px; color: #334155; margin-top: 10px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #475569; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(71,85,105,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>📋 Kết quả xét duyệt hồ sơ</div>
                </div>
                <div class='content'>
                    <h2>Xin chào {$applicantName},</h2>
                    <p>Cảm ơn bạn đã quan tâm và nộp hồ sơ đăng ký trở thành <strong>Đối tác Giảng viên</strong> trên nền tảng <strong>MindHub</strong>.</p>
                    
                    <p>Sau khi xem xét kỹ lưỡng hồ sơ chuyên môn của bạn, Ban Quản Trị rất tiếc phải thông báo rằng yêu cầu nâng cấp tài khoản của bạn <strong>chưa được phê duyệt</strong> tại thời điểm này.</p>
                    
                    <div class='card'>
                        <div class='title'>Thông tin tài khoản đăng ký:</div>
                        <div class='info-item'>✉️ Email tài khoản: <strong>{$applicantEmail}</strong></div>
                        <div class='info-item'>📱 Số điện thoại liên hệ: <strong>{$applicantPhone}</strong></div>
                        
                        <div class='title' style='margin-top: 14px;'>Lý do từ Ban Quản Trị:</div>
                        <div class='reason-box'>
                            <em>\"{$reasonText}\"</em>
                        </div>
                    </div>

                    <p>Đừng nản lòng! Bạn hoàn toàn có thể bổ sung, cập nhật thêm thông tin giới thiệu, kinh nghiệm chuyên môn và gửi lại hồ sơ xét duyệt bất cứ lúc nào:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$resubmitUrl}' class='btn' target='_blank'>Kiểm Tra & Nộp Lại Hồ Sơ</a>
                    </div>

                    <p style='font-size: 13px; color: #64748b;'>Nếu bạn có câu hỏi hoặc cần giải đáp thêm về tiêu chuẩn giảng viên, vui lòng phản hồi email này để nhận trợ giúp từ ban hỗ trợ.</p>
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
                    $message->embedFromPath($logoFile, $cidName);
                }
            });
    }
}
