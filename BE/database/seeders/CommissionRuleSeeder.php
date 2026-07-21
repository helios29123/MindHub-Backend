<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use Illuminate\Database\Seeder;

final class CommissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['marketplace_default', 70, 30, 'Marketplace mặc định'],
            ['platform_ads', 37, 63, 'Nền tảng tự chạy quảng cáo'],
            ['admin_campaign', 37, 63, 'Chiến dịch quảng bá của admin'],
            ['instructor_coupon', 97, 3, 'Mua bằng coupon do giảng viên tạo'],
            ['instructor_referral', 97, 3, 'Link giới thiệu của giảng viên'],
        ];

        foreach ($rules as [$channel, $ins, $plat, $desc]) {
            CommissionRule::query()->updateOrCreate(
                ['sale_channel' => $channel],
                [
                    'instructor_rate' => $ins,
                    'platform_rate' => $plat,
                    'description' => $desc,
                    'is_active' => true
                ]
            );
        }
    }
}
