<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'learner.locked@mindhub.test'],
            [
                'full_name' => 'Learner Locked',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000002',
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_LOCKED,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => true,
                'locked_reason' => 'Seed locked user for testing',
                'password_reset' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'learner.inactive@mindhub.test'],
            [
                'full_name' => 'Learner Inactive',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000003',
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_INACTIVE,
                'email_verified_at' => null,
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'oauth.only@mindhub.test'],
            [
                'full_name' => 'OAuth Only User',
                'password_hash' => null,
                'phone' => '0900000004',
                'oauth_account_login' => 'google-oauth-only-001',
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'email.exists@mindhub.test'],
            [
                'full_name' => 'Email Exists User',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000005',
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@mindhub.test'],
            [
                'full_name' => 'MindHub Admin',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000001',
                'oauth_account_login' => null,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'learner1@mindhub.test'],
            [
                'full_name' => 'Lê Gia Bảo',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0900000004',
                'oauth_account_login' => null,
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'instructor1@mindhub.test'],
            [
                'full_name' => 'Giảng viên MindHub 01',
                'password_hash' => Hash::make('12345678'),
                'phone' => '0987654321',
                'oauth_account_login' => null,
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'locked' => false,
                'locked_reason' => null,
                'password_reset' => null,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $instructor = User::withTrashed()->where('email', 'instructor1@mindhub.test')->first();
        if ($instructor && $instructor->trashed()) {
            $instructor->restore();
        }
        $instructorId = $instructor ? $instructor->id : (int) DB::table('users')->where('email', 'instructor1@mindhub.test')->value('id');

        if ($instructorId) {
            \App\Models\InstructorProfile::firstOrCreate(
                ['user_id' => $instructorId],
                [
                    'bio' => 'Giảng viên chuyên nghiệp về lập trình và thiết kế.',
                    'expertise' => 'Web Development',
                    'experience_years' => 5,
                    'level' => 'Senior',
                ]
            );

            $payoutData = [
                'provider' => 'Techcombank – Ngân hàng TMCP Kỹ thương Việt Nam',
                'account_name' => 'GIẢNG VIÊN MINDHUB 01',
                'status' => 'active',
                'connected_at' => now(),
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('payout_accounts', 'is_default')) {
                $payoutData['is_default'] = true;
            }

            \App\Models\PayoutAccount::firstOrCreate(
                ['user_id' => $instructorId, 'account_number' => '1903123456789'],
                $payoutData
            );
        }
    }
}
