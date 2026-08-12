<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Course $course,
        public readonly ?object $order = null
    ) {
    }

    public function build(): self
    {
        $userName = e($this->user->full_name);
        $courseTitle = e($this->course->title);
        $instructorName = e($this->course->instructor?->full_name ?? 'Giảng viên MindHub');
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        
        $learnUrl = "{$frontendUrl}/learning/{$this->course->id}";
        
        $orderCode = e($this->order->order_code ?? ('MH-ORD-' . ($this->order->id ?? rand(1000, 9999))));
        $amountRaw = (float) ($this->order->amount ?? $this->course->sale_price ?? $this->course->price ?? 0);
        $amountFormatted = number_format($amountRaw, 0, ',', '.') . ' VNĐ';

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; }
                .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
                .logo-img { height: 68px; max-width: 300px; object-fit: contain; margin: 0 auto 8px auto; display: block; }
                .welcome-badge { display: inline-block; background-color: #ecfdf5; color: #059669; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .course-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .course-title { font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px; }
                .course-info { font-size: 13px; color: #64748b; margin-bottom: 4px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #0066FF; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(0,102,255,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
                .receipt-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
                .receipt-table td { padding: 8px 0; border-bottom: 1px dashed #e2e8f0; }
                .receipt-table tr:last-child td { border-bottom: none; font-weight: 700; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='welcome-badge'>🎉 Đăng ký khóa học thành công</div>
                </div>
                <div class='content'>
                    <h2>Xin chào <strong>{$userName}</strong>,</h2>
                    <p>Chúc mừng bạn đã đăng ký thành công khóa học trên nền tảng tri thức <strong>MindHub</strong>!</p>
                    
                    <div class='course-card'>
                        <div class='course-title'>{$courseTitle}</div>
                        <div class='course-info'>👨‍🏫 Giảng viên hướng dẫn: <strong>{$instructorName}</strong></div>
                        <div class='course-info'>🔖 Mã đơn hàng: <strong style='font-family: monospace;'>{$orderCode}</strong></div>
                    </div>

                    <table class='receipt-table'>
                        <tr>
                            <td style='color: #64748b;'>Mã hóa đơn:</td>
                            <td style='text-align: right; font-family: monospace;'>{$orderCode}</td>
                        </tr>
                        <tr>
                            <td style='color: #64748b;'>Khóa học đăng ký:</td>
                            <td style='text-align: right; font-weight: 600;'>{$courseTitle}</td>
                        </tr>
                        <tr>
                            <td style='color: #64748b;'>Học phí đã thanh toán:</td>
                            <td style='text-align: right; color: #059669;'>{$amountFormatted}</td>
                        </tr>
                    </table>

                    <p style='margin-top: 24px;'>Khóa học của bạn đã được kích hoạt thành công. Bạn có thể bắt đầu học ngay bây giờ để chinh phục những kỹ năng mới:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$learnUrl}' class='btn' target='_blank'>Vào Học Khóa Này Ngay</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>Nếu bạn cần hỗ trợ thêm, vui lòng liên hệ bộ phận chăm sóc học viên của MindHub.</p>
                    <p>&copy; " . date('Y') . " MindHub E-Learning Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this
            ->subject("[MindHub] Chào mừng bạn đến với khóa học: {$this->course->title}")
            ->html($html)
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($logoFile, $cidName) {
                if (file_exists($logoFile)) {
                    $message->embedFromPath($logoFile, $cidName);
                }
            });
    }
}
