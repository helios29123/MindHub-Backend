<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\PayoutAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApprovedInstructorTestSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // ==========================================
        // 1. TÀI KHOẢN GIẢNG VIÊN ĐÃ ĐƯỢC PHÊ DUYỆT ĐẦY ĐỦ
        // ==========================================
        $instructor = User::updateOrCreate(
            ['email' => 'giangvien.test@mindhub.vn'],
            [
                'full_name' => 'Nguyễn Thành Nam (Giảng viên)',
                'password_hash' => $defaultPassword,
                'phone' => '0988776655',
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => now(),
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        InstructorProfile::updateOrCreate(
            ['user_id' => $instructor->id],
            [
                'bio' => 'Chuyên gia Fullstack Web & Cloud với 8 năm kinh nghiệm thực chiến phát triển các hệ thống lớn bằng Laravel, React, Docker.',
                'expertise' => 'Lập trình Web Fullstack (Laravel, ReactJS, TypeScript)',
                'experience_years' => 8,
                'instructor_rank' => 'gold',
            ]
        );

        PayoutAccount::where('user_id', $instructor->id)->delete();
        PayoutAccount::create([
            'user_id' => $instructor->id,
            'account_number' => '0071009998888',
            'provider' => 'Vietcombank - Ngân hàng TMCP Ngoại thương Việt Nam',
            'account_name' => 'NGUYEN THANH NAM',
            'status' => 'verified',
            'is_default' => true,
            'verified_at' => now(),
            'disabled_at' => null,
        ]);

        // Đảm bảo cả tài khoản phụ quen thuộc giangvien@mindhub.test cũng có thể dùng được
        $instructorLegacy = User::where('email', 'giangvien@mindhub.test')->first();
        if ($instructorLegacy) {
            $instructorLegacy->update([
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'password_hash' => $defaultPassword,
                'email_verified_at' => now(),
            ]);
        } else {
            $instructorLegacy = User::create([
                'email' => 'giangvien@mindhub.test',
                'full_name' => 'Giảng viên MindHub',
                'password_hash' => $defaultPassword,
                'phone' => '0987111222',
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => now(),
            ]);
        }

        InstructorProfile::updateOrCreate(
            ['user_id' => $instructorLegacy->id],
            [
                'bio' => 'Giảng viên chuyên nghiệp tại MindHub.',
                'expertise' => 'Lập trình Web',
                'experience_years' => 5,
                'instructor_rank' => 'gold',
            ]
        );

        PayoutAccount::where('user_id', $instructorLegacy->id)->delete();
        PayoutAccount::create([
            'user_id' => $instructorLegacy->id,
            'account_number' => '1903123456789',
            'provider' => 'Techcombank',
            'account_name' => 'GIANG VIEN MINDHUB',
            'status' => 'verified',
            'is_default' => true,
            'verified_at' => now(),
            'disabled_at' => null,
        ]);

        // ==========================================
        // 2. TÀI KHOẢN ADMIN ĐỂ KIỂM DUYỆT KHÓA HỌC
        // ==========================================
        $admin = User::where('email', 'admin@mindhub.vn')->first();
        if ($admin) {
            $admin->update([
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'password_hash' => $defaultPassword,
                'email_verified_at' => now(),
            ]);
        } else {
            User::create([
                'email' => 'admin@mindhub.vn',
                'full_name' => 'Quản Trị Viên MindHub',
                'password_hash' => $defaultPassword,
                'phone' => '0901234567',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => now(),
            ]);
        }

        // Cập nhật cả admin@mindhub.test để đăng nhập tài khoản nào cũng thành công với pass: password123
        $adminTest = User::where('email', 'admin@mindhub.test')->first();
        if ($adminTest) {
            $adminTest->update([
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'password_hash' => $defaultPassword,
                'email_verified_at' => now(),
            ]);
        } else {
            User::create([
                'email' => 'admin@mindhub.test',
                'full_name' => 'Administrator MindHub',
                'password_hash' => $defaultPassword,
                'phone' => '0999999999',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'locked' => false,
                'locked_reason' => null,
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info("Approved Instructor and Admin accounts seeded successfully!");
        $this->command->info("Instructor: giangvien.test@mindhub.vn / password123");
        $this->command->info("Admin: admin@mindhub.vn / password123");
    }
}
