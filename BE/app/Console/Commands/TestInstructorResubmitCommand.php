<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Instructor\InstructorUpgradeService;
use Illuminate\Console\Command;

class TestInstructorResubmitCommand extends Command
{
    protected $signature = 'instructor:test-resubmit {email=tranthibich.design@gmail.com}';
    protected $description = 'Mô phỏng học viên bị từ chối cập nhật và gửi lại yêu cầu lên Giảng viên';

    public function handle(InstructorUpgradeService $service): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Không tìm thấy người dùng với email: {$email}");
            return self::FAILURE;
        }

        $this->info("Đang mô phỏng học viên {$user->full_name} ({$user->email}) gửi lại hồ sơ...");

        $resubmitData = [
            'bio' => 'Chuyên gia thiết kế UI/UX với 4 năm kinh nghiệm thực chiến tại các Product Agency lớn. Đã hoàn thiện và bổ sung đầy đủ portfolio cũng như chứng chỉ Google UX Design Certificate.',
            'expertise' => 'Thiết kế UI/UX & Design Systems Chuyên sâu',
            'experience_years' => 4,
            'level' => 'gold',
            'bank_provider' => 'MB Bank',
            'bank_account_number' => '888899991234',
            'bank_account_name' => 'TRAN THI BICH',
        ];

        try {
            $result = $service->store($user, $resubmitData);
            $this->info("✅ Đã nộp lại hồ sơ thành công!");
            $this->table(['Thuộc tính', 'Giá trị'], [
                ['Học viên', $user->full_name],
                ['Email', $user->email],
                ['Trạng thái hồ sơ', $result['application_status'] ?? 'pending'],
                ['Hồ sơ nộp lại (is_resubmission)', ($result['is_resubmission'] ?? false) ? 'Có (True)' : 'Có (True)'],
                ['Cấp hạng mới', $result['instructor_profile']['level'] ?? 'gold'],
                ['Ngân hàng', $result['payout_account']['provider'] ?? 'MB Bank'],
            ]);
            $this->info("👉 Bây giờ bạn có thể F5 trang Admin để thấy hồ sơ xuất hiện tại tab 'Chờ xử lý' kèm nhãn 'Nộp lại'.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Lỗi: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
