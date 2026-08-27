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
                'course_id' => DB::table('courses')->value('id') ?? 1,
                'code' => 'CAT10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'usage_limit' => 100,
                'used_count' => 0,
                'start_at' => $now->copy()->subDays(30),
                'end_at' => $now->copy()->addDays(30),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
