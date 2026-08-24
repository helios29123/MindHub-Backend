<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalSuccessInstructorMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly WithdrawRequest $withdrawal,
        public readonly User $instructor
    ) {
    }

    public function build(): self
    {
        $instructorName = e($this->instructor->full_name ?? $this->instructor->name ?? 'Giảng viên');
        $amountFormatted = number_format($this->withdrawal->amount, 0, ',', '.') . ' đ';
        $typeLabel = $this->withdrawal->type === WithdrawRequest::TYPE_EARLY_WITHDRAWAL ? 'Rút tiền sớm' : 'Thanh toán định kỳ';
        $bankName = e($this->withdrawal->bank_name ?? 'Ngân hàng');
        $accountNumber = e($this->withdrawal->account_number_snapshot ?? 'Chưa cập nhật');
        $accountName = e($this->withdrawal->account_name_snapshot ?? 'Chưa cập nhật');
        $paidAt = $this->withdrawal->paid_at 
            ? $this->withdrawal->paid_at->format('d/m/Y H:i:s') 
            : date('d/m/Y H:i:s');
        $refCode = e($this->withdrawal->provider_payout_id ?? "MH-PAY-{$this->withdrawal->id}");

        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $historyUrl = "{$frontendUrl}/instructor/withdrawals";

        $logoFile = base_path('mindhub.jpg');
        if (!file_exists($logoFile)) {
            $logoFile = public_path('images/mindhub-logo.jpg');
        }
        $cidName = 'mindhub-logo';

        return $this->subject("[MindHub] Chuyển tiền thành công - Yêu cầu rút tiền #{$this->withdrawal->id}")
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
                .badge { display: inline-block; background-color: #dcfce7; color: #166534; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; margin-top: 4px; }
                .content { padding: 24px 0; font-size: 15px; line-height: 1.6; }
                .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin: 20px 0; }
                .amount-box { text-align: center; background: #16a34a; color: #ffffff; padding: 16px; border-radius: 12px; font-weight: 800; font-size: 22px; margin-bottom: 16px; }
                .title { font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 12px; }
                .info-item { font-size: 14px; color: #475569; margin-bottom: 8px; }
                .btn-wrapper { text-align: center; margin: 28px 0; }
                .btn { display: inline-block; background-color: #16a34a; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; box-shadow: 0 4px 10px rgba(22,163,74,0.25); }
                .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:{$cidName}' alt='MindHub Logo' class='logo-img' />
                    <div class='badge'>✅ Thanh toán thành công</div>
                </div>
                <div class='content'>
                    <h2>Xin chào {$instructorName},</h2>
                    <p>Hệ thống <strong>MindHub</strong> xin thông báo: Yêu cầu chuyển tiền/rút tiền của bạn đã được thực hiện thành công vào tài khoản ngân hàng.</p>
                    
                    <div class='card'>
                        <div class='amount-box'>+ {$amountFormatted}</div>
                        <div class='title'>Chi tiết giao dịch chuyển tiền #{$this->withdrawal->id}</div>
                        <div class='info-item'>📌 Loại yêu cầu: <strong>{$typeLabel}</strong></div>
                        <div class='info-item'>🏦 Ngân hàng thụ hưởng: <strong>{$bankName}</strong></div>
                        <div class='info-item'>💳 Số tài khoản: <strong>{$accountNumber}</strong></div>
                        <div class='info-item'>📛 Tên chủ tài khoản: <strong>{$accountName}</strong></div>
                        <div class='info-item'>🔖 Mã tham chiếu: <strong>{$refCode}</strong></div>
                        <div class='info-item'>⏰ Thời gian hoàn tất: <strong>{$paidAt}</strong></div>
                    </div>

                    <p>Cảm ơn bạn đã luôn đồng hành và đóng góp những khóa học chất lượng cho cộng đồng <strong>MindHub</strong>!</p>
                    
                    <div class='btn-wrapper'>
                        <a href='{$historyUrl}' class='btn' target='_blank'>Xem Lịch Sử Thanh Toán</a>
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
