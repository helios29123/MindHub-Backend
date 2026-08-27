<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use Illuminate\Database\Seeder;

final class CommissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        CommissionRule::query()->updateOrCreate(
            ['name' => 'Default Rule'],
            [
                'instructor_rate' => 0.7000,
                'platform_rate' => 0.3000,
                'description' => 'Marketplace mặc định',
                'is_active' => true
            ]
        );
        
        CommissionRule::query()->updateOrCreate(
            ['name' => 'Legacy Rule'],
            [
                'instructor_rate' => 0.5000,
                'platform_rate' => 0.5000,
                'description' => 'Luật cũ',
                'is_active' => false
            ]
        );
    }
}
