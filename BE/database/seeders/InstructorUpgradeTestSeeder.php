<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\PayoutAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorUpgradeTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Target Learner -> Pending Verification (Nộp lại hồ sơ)
        $user1 = User::firstOrNew(['email' => 'dangdominh303@gmail.com']);
        $user1->full_name = 'Đặng Đỗ Minh';
        $user1->role = 'learner';
        $user1->status = 'active';
        $user1->avatar_url = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80';
        $user1->email_verified_at = $user1->email_verified_at ?? now();
        if (empty($user1->phone)) {
            $user1->phone = '0981112233';
        }
        if (empty($user1->password_hash)) {
            $user1->password_hash = Hash::make('password123');
        }
        $user1->save();

        $profile1 = InstructorProfile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'bio' => 'Kỹ sư phần mềm với hơn 5 năm kinh nghiệm phát triển hệ thống web Fullstack và Cloud. Đam mê chia sẻ kiến thức thực chiến về React, Laravel và CI/CD.',
                'expertise' => 'Lập trình Web Fullstack (Laravel, React, NodeJS)',
                'experience_years' => 5,
                'instructor_rank' => 'diamond',
            ]
        );
        // Set created_at earlier than updated_at to simulate a resubmitted application
        $profile1->created_at = now()->subDays(5);
        $profile1->updated_at = now();
        $profile1->save();

        PayoutAccount::updateOrCreate(
            [
                'user_id' => $user1->id,
                'provider' => 'Techcombank',
                'account_number' => '19036789123456',
            ],
            [
                'account_name' => 'DANG DO MINH',
                'status' => 'pending_verification',
                'is_default' => false,
                'verified_at' => null,
                'disabled_at' => null,
            ]
        );

        // 2. Approved Instructor
        $user2 = User::firstOrNew(['email' => 'nguyenvanan.dev@gmail.com']);
        $user2->full_name = 'Nguyễn Văn An';
        $user2->role = 'instructor';
        $user2->status = 'active';
        $user2->email_verified_at = $user2->email_verified_at ?? now()->subMonths(2);
        if (empty($user2->phone)) {
            $user2->phone = '0982223344';
        }
        if (empty($user2->password_hash)) {
            $user2->password_hash = Hash::make('password123');
        }
        $user2->save();

        InstructorProfile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'bio' => 'Chuyên gia Trí tuệ nhân tạo và Machine Learning với hơn 6 năm nghiên cứu và triển khai giải pháp AI thực tế cho các doanh nghiệp công nghệ lớn.',
                'expertise' => 'Trí tuệ nhân tạo (AI), Machine Learning & Deep Learning',
                'experience_years' => 6,
                'instructor_rank' => 'gold',
            ]
        );

        PayoutAccount::updateOrCreate(
            [
                'user_id' => $user2->id,
                'provider' => 'Vietcombank',
                'account_number' => '0071001234567',
            ],
            [
                'account_name' => 'NGUYEN VAN AN',
                'status' => 'verified',
                'is_default' => true,
                'verified_at' => now()->subMonths(1),
                'disabled_at' => null,
            ]
        );

        // 3. Rejected Upgrade Request
        $user3 = User::firstOrNew(['email' => 'tranthibich.design@gmail.com']);
        $user3->full_name = 'Trần Thị Bích';
        $user3->role = 'learner';
        $user3->status = 'active';
        $user3->email_verified_at = $user3->email_verified_at ?? now()->subDays(15);
        if (empty($user3->phone)) {
            $user3->phone = '0983334455';
        }
        if (empty($user3->password_hash)) {
            $user3->password_hash = Hash::make('password123');
        }
        $user3->save();

        InstructorProfile::updateOrCreate(
            ['user_id' => $user3->id],
            [
                'bio' => 'Product Designer với đam mê thiết kế trải nghiệm người dùng hiện đại và hệ thống Design System chuẩn quốc tế.',
                'expertise' => 'UI/UX Design & Product Strategy',
                'experience_years' => 2,
                'instructor_rank' => 'bronze',
            ]
        );

        PayoutAccount::updateOrCreate(
            [
                'user_id' => $user3->id,
                'provider' => 'MB Bank',
                'account_number' => '888899991234',
            ],
            [
                'account_name' => 'TRAN THI BICH',
                'status' => 'disabled',
                'is_default' => false,
                'verified_at' => null,
                'disabled_at' => now()->subDays(3),
            ]
        );

        // 4. Another Pending Learner
        $user4 = User::firstOrNew(['email' => 'lehoangnam.dev@gmail.com']);
        $user4->full_name = 'Lê Hoàng Nam';
        $user4->role = 'learner';
        $user4->status = 'active';
        $user4->email_verified_at = $user4->email_verified_at ?? now()->subDays(2);
        if (empty($user4->phone)) {
            $user4->phone = '0984445566';
        }
        if (empty($user4->password_hash)) {
            $user4->password_hash = Hash::make('password123');
        }
        $user4->save();

        InstructorProfile::updateOrCreate(
            ['user_id' => $user4->id],
            [
                'bio' => 'Lập trình viên Mobile chuyên sâu về Flutter và React Native, đã xuất bản nhiều ứng dụng đạt hàng trăm nghìn lượt tải trên App Store & Google Play.',
                'expertise' => 'Lập trình Di động (Flutter, React Native)',
                'experience_years' => 3,
                'instructor_rank' => 'silver',
            ]
        );

        PayoutAccount::updateOrCreate(
            [
                'user_id' => $user4->id,
                'provider' => 'VPBank',
                'account_number' => '999888777123',
            ],
            [
                'account_name' => 'LE HOANG NAM',
                'status' => 'pending_verification',
                'is_default' => false,
                'verified_at' => null,
                'disabled_at' => null,
            ]
        );

        $this->command->info('Instructor Upgrade quick test data seeded successfully.');
    }
}
