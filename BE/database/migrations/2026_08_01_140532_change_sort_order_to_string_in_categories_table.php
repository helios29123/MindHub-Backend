<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('sort_order', 100)->default('000100')->change();
        });

        // Format existing integer sort_orders into 6-digit padded strings (e.g., 1 -> '000100')
        $categories = \Illuminate\Support\Facades\DB::table('categories')->get();
        foreach ($categories as $cat) {
            $num = (int)$cat->sort_order;
            if ($num > 0) {
                $newSort = str_pad((string)($num * 100), 6, '0', STR_PAD_LEFT);
                \Illuminate\Support\Facades\DB::table('categories')
                    ->where('id', $cat->id)
                    ->update(['sort_order' => $newSort]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->change();
        });
    }
};
