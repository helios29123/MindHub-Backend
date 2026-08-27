<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'learner.active@mindhub.test'],
            [
                'full_name' => 'Learner Active',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000001',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'learner.locked@mindhub.test'],
            [
                'full_name' => 'Learner Locked',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000002',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => true,
                'locked_reason' => 'Seed locked user for testing',
            ]
        );

        User::updateOrCreate(
            ['email' => 'learner.inactive@mindhub.test'],
            [
                'full_name' => 'Learner Inactive',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000003',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => null,
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'oauth.only@mindhub.test'],
            [
                'full_name' => 'OAuth Only User',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000004',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'email.exists@mindhub.test'],
            [
                'full_name' => 'Email Exists User',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000005',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
            ]
        );

        $instructor = User::updateOrCreate(
            ['email' => 'instructor1@mindhub.test'],
            [
                'full_name' => 'Giảng viên MindHub 01',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0987654321',
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
            ]
        );

        \App\Models\InstructorProfile::firstOrCreate(
            ['user_id' => $instructor->id],
            [
                'bio' => 'Giảng viên chuyên nghiệp về lập trình và thiết kế.',
                'expertise' => 'Web Development',
                'experience_years' => 5,
            ]
        );

        \App\Models\PayoutAccount::firstOrCreate(
            ['user_id' => $instructor->id, 'account_number' => '1903123456789'],
            [
                'provider' => 'Techcombank – Ngân hàng TMCP Kỹ thương Việt Nam',
                'account_name' => 'GIẢNG VIÊN MINDHUB 01',
                'status' => 'verified',
                'is_default' => true,
                'verified_at' => now(),
            ]
        );
    }
}
