<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CatalogUsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'full_name' => 'CAT Admin',
                'email' => 'cat.admin@example.com',
                'role' => 'admin',
                'status' => 'active',
                'phone' => '0900000000',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Instructor Active 01',
                'email' => 'cat.instructor01@example.com',
                'role' => 'instructor',
                'status' => 'active',
                'phone' => '0900000001',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Instructor Active 02',
                'email' => 'cat.instructor02@example.com',
                'role' => 'instructor',
                'status' => 'active',
                'phone' => '0900000002',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Instructor Inactive',
                'email' => 'cat.instructor.inactive@example.com',
                'role' => 'instructor',
                'status' => 'inactive',
                'phone' => '0900000003',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Instructor Locked',
                'email' => 'cat.instructor.locked@example.com',
                'role' => 'instructor',
                'status' => 'locked',
                'phone' => '0900000004',
                'locked' => true,
                'locked_reason' => 'Seeder test locked instructor',
            ],
            [
                'full_name' => 'CAT Learner 01',
                'email' => 'cat.learner01@example.com',
                'role' => 'learner',
                'status' => 'active',
                'phone' => '0910000001',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Learner 02',
                'email' => 'cat.learner02@example.com',
                'role' => 'learner',
                'status' => 'active',
                'phone' => '0910000002',
                'locked' => false,
            ],
            [
                'full_name' => 'CAT Learner 03',
                'email' => 'cat.learner03@example.com',
                'role' => 'learner',
                'status' => 'active',
                'phone' => '0910000003',
                'locked' => false,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'full_name' => $user['full_name'],
                    'password_hash' => Hash::make('12345678'),
                    'phone' => $user['phone'],
                    'oauth_account_login' => null,
                    'role' => $user['role'],
                    'status' => $user['status'],
                    'email_verified_at' => $now,
                    'last_login_at' => $now,
                    'locked' => $user['locked'],
                    'locked_reason' => $user['locked_reason'] ?? null,
                    'password_reset' => null,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
