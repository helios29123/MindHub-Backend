<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDemoUpdateSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('minhdang3010');

        // 1. Admin
        $admin = User::updateOrCreate(
            ['email' => 'dominhdang3010@gmail.com'],
            [
                'full_name' => 'Quản trị viên MindHub',
                'password_hash' => $password,
                'phone' => '0912345999',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'locked' => false,
            ]
        );
        $this->command?->info("Admin updated: {$admin->email} (ID: {$admin->id}, Role: {$admin->role})");

        // 2. Instructor
        $instructor = User::updateOrCreate(
            ['email' => 'dangdevbe2026@gmail.com'],
            [
                'full_name' => 'ThS. Lê Hoàng Nam',
                'password_hash' => $password,
                'phone' => '0912345001',
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'locked' => false,
            ]
        );

        InstructorProfile::updateOrCreate(
            ['user_id' => $instructor->id],
            [
                'bio' => 'Giảng viên cấp cao MindHub, chuyên gia kiến trúc phần mềm và hệ thống phân tán.',
                'expertise' => 'Backend & Cloud Computing',
                'experience_years' => 8,
                'instructor_rank' => 'Gold',
            ]
        );
        $this->command?->info("Instructor updated: {$instructor->email} (ID: {$instructor->id}, Role: {$instructor->role})");

        // 3. Learner
        $learner = User::updateOrCreate(
            ['email' => 'dangdominh303@gmail.com'],
            [
                'full_name' => 'Đỗ Minh Đăng',
                'password_hash' => $password,
                'phone' => '0901234303',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'locked' => false,
            ]
        );
        $this->command?->info("Learner updated: {$learner->email} (ID: {$learner->id}, Role: {$learner->role})");
    }
}
