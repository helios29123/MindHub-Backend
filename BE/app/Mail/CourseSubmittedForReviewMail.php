<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseSubmittedForReviewMail extends Mailable
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
        $instructorEmail = e($this->instructor->email);
        $courseTitle = e($this->course->title);
        $categoriesList = $this->course->categories;
        if ($categoriesList && $categoriesList->count() > 0) {
            $categoryName = e($categoriesList->pluck('name')->implode(', '));
        } else {
            $categoryName = e($this->course->category?->name ?? 'Chưa phân loại');
        }
        $level = e($this->course->course_level ?? 'Mọi trình độ');
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $adminReviewUrl = "{$frontendUrl}/admin/courses";

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject("[MindHub Admin] Yêu cầu duyệt khóa học: {$courseTitle}")
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
                .badge { display: inline-block; background-color: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
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
                    <div class='badge'>📩 Thông báo yêu cầu duyệt khóa học</div>
                </div>
                <div class='content'>
                    <h2>Kính gửi Quản trị viên,</h2>
                    <p>Giảng viên <strong>{$instructorName}</strong> ({$instructorEmail}) vừa gửi một khóa học mới lên hệ thống và đang chờ Admin xét duyệt.</p>
                    
                    <div class='card'>
                        <div class='title'>{$courseTitle}</div>
                        <div class='info-item'>👨‍🏫 Giảng viên: <strong>{$instructorName}</strong> ({$instructorEmail})</div>
                        <div class='info-item'>📁 Danh mục: <strong>{$categoryName}</strong></div>
                        <div class='info-item'>📊 Trình độ: <strong>{$level}</strong></div>
                        <div class='info-item'>🕒 Thời gian gửi: <strong>" . date('H:i:s d/m/Y') . "</strong></div>
                    </div>

                    <p>Vui lòng kiểm tra nội dung bài giảng, danh mục và các thông tin liên quan trước khi phê duyệt hiển thị trên sàn MindHub:</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$adminReviewUrl}' class='btn' target='_blank'>Xem & Duyệt Khóa Học Này</a>
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
