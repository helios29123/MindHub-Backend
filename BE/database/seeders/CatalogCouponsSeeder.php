<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCouponsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('coupons')->updateOrInsert(
            ['code' => 'CAT10'],
            [
                'user_id' => null,
                'course_id' => null,
                'code' => 'CAT10',
                'name' => 'CAT Coupon Active 10%',
                'description' => 'Coupon active dùng test order cho Catalog.',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_order_amount' => 100000,
                'usage_limit' => 100,
                'used_count' => 0,
                'start_at' => $now->copy()->subDays(30),
                'end_at' => $now->copy()->addDays(30),
                'status' => 'active',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
