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
    }
}
