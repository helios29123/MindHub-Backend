<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogInstructorProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $profiles = [
            'cat.instructor01@example.com' => [
                'bio' => 'Giảng viên Laravel/PHP có kinh nghiệm xây dựng REST API thực tế.',
                'expertise' => 'Laravel, PHP, MySQL, REST API',
                'experience_years' => 6,
                'level' => 'senior',
            ],
            'cat.instructor02@example.com' => [
                'bio' => 'Giảng viên Frontend chuyên React và UI Design.',
                'expertise' => 'React, JavaScript, UI/UX',
                'experience_years' => 5,
                'level' => 'middle',
            ],
            'cat.instructor.inactive@example.com' => [
                'bio' => 'Profile test instructor inactive.',
                'expertise' => 'Testing',
                'experience_years' => 2,
                'level' => 'junior',
            ],
            'cat.instructor.locked@example.com' => [
                'bio' => 'Profile test instructor locked.',
                'expertise' => 'Testing',
                'experience_years' => 3,
                'level' => 'middle',
            ],
        ];

        foreach ($profiles as $email => $profile) {
            $userId = (int) DB::table('users')->where('email', $email)->value('id');

            if (!$userId) {
                continue;
            }

            DB::table('instructor_profiles')->updateOrInsert(
                ['user_id' => $userId],
                array_merge($profile, [
                    'user_id' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
