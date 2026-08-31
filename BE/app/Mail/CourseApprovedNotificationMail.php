<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseApprovedNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $instructor,
        public readonly Course $course
    ) {
    }

    public function build(): self
    {
        $instructorName = e($this->instructor->full_name);
        $courseTitle = e($this->course->title);
        
        $categoriesList = $this->course->categories;
        if ($categoriesList && $categoriesList->count() > 0) {
            $categoryName = e($categoriesList->pluck('name')->implode(', '));
        } else {
            $categoryName = e($this->course->category?->name ?? 'Khoa học tổng hợp');
        }

        $priceRaw = (float) ($this->course->sale_price ?? $this->course->price ?? 0);
        $priceText = $priceRaw == 0 ? 'Miễn phí' : (number_format($priceRaw, 0, ',', '.') . ' VNĐ');
        
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $courseUrl = "{$frontendUrl}/courses/" . ($this->course->slug ?: $this->course->id);
        $instructorDashboardUrl = "{$frontendUrl}/instructor/courses";

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject("🎉 [MindHub] Khóa học \"{$courseTitle}\" của bạn đã được duyệt xuất bản!")
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
                .badge { display: inline-block; background-color: #ecfdf5; color: #059669; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .title { font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px; }
                .info-item { font-size: 13px; color: #64748b; margin-bottom: 6px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #059669; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(5,150,105,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>🎉 Khóa học đã được phê duyệt</div>
                </div>
                <div class='content'>
                    <h2>Xin chào Giảng viên <strong>{$instructorName}</strong>,</h2>
                    <p>Chúc mừng bạn! Khóa học <strong>{$courseTitle}</strong> của bạn vừa được ban quản trị MindHub chính thức phê duyệt và xuất bản công khai trên nền tảng.</p>
                    
                    <div class='card'>
                        <div class='title'>{$courseTitle}</div>
                        <div class='info-item'>📁 Danh mục: <strong>{$categoryName}</strong></div>
                        <div class='info-item'>🏷️ Học phí niêm yết: <strong style='color: #059669;'>{$priceText}</strong></div>
                        <div class='info-item'>🕒 Thời gian xuất bản: <strong>" . date('H:i:s d/m/Y') . "</strong></div>
                    </div>

                    <p>Hiện tại học viên trên toàn hệ thống đã có thể tìm thấy và đăng ký tham gia khóa học của bạn. Bạn có thể theo dõi tiến độ học tập và doanh thu trực tiếp từ trang Dashboard Giảng viên:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$courseUrl}' class='btn' target='_blank'>Xem Khóa Học Trên Sàn</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>Cảm ơn bạn đã đóng góp nội dung chất lượng cho cộng đồng MindHub!</p>
                    <p>&copy; " . date('Y') . " MindHub E-Learning Platform. All rights reserved.</p>
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
