<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseRejectedNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $instructor,
        public readonly Course $course,
        public readonly string $reason
    ) {
    }

    public function build(): self
    {
        $instructorName = e($this->instructor->full_name);
        $courseTitle = e($this->course->title);
        $rejectionReason = nl2br(e($this->reason));
        
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $editCourseUrl = "{$frontendUrl}/instructor/courses/{$this->course->id}/edit";

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject("⚠️ [MindHub] Yêu cầu duyệt khóa học \"{$courseTitle}\" cần được điều chỉnh")
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
                .badge { display: inline-block; background-color: #fef2f2; color: #dc2626; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #fff5f5; border: 1px solid #fecaca; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .title { font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px; }
                .reason-box { background: #ffffff; border-left: 4px solid #dc2626; padding: 14px 16px; margin-top: 12px; border-radius: 4px; font-size: 14px; color: #334155; line-height: 1.5; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #0284c7; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(2,132,199,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>⚠️ Khóa học cần điều chỉnh</div>
                </div>
                <div class='content'>
                    <h2>Xin chào Giảng viên <strong>{$instructorName}</strong>,</h2>
                    <p>Ban quản trị MindHub đã xem xét khóa học <strong>\"{$courseTitle}\"</strong> do bạn gửi duyệt. Rất tiếc, khóa học hiện chưa đáp ứng đầy đủ tiêu chuẩn chất lượng của sàn để xuất bản công khai.</p>
                    
                    <div class='card'>
                        <div class='title'>Khóa học: {$courseTitle}</div>
                        <p style='margin: 0; font-size: 13.5px; color: #dc2626; font-weight: 700;'>Lý do phản hồi từ Ban Kiểm Duyệt:</p>
                        <div class='reason-box'>
                            {$rejectionReason}
                        </div>
                    </div>

                    <p>Bạn vui lòng truy cập trình quản lý khóa học để cập nhật, hoàn thiện lại các nội dung theo yêu cầu trên, sau đó gửi lại yêu cầu kiểm duyệt để được xuất bản nhé:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$editCourseUrl}' class='btn' target='_blank'>Chỉnh Sửa & Cập Nhật Khóa Học</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>Nếu có thắc mắc, vui lòng liên hệ bộ phận Hỗ trợ Giảng viên MindHub qua email: support@mindhub.vn</p>
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
