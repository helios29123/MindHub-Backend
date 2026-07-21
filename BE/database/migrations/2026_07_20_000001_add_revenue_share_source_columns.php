<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'sale_source')) {
                    $table->string('sale_source', 100)->nullable()->index()->after('amount');
                }
                if (!Schema::hasColumn('orders', 'commission_rule_id')) {
                    $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete()->after('sale_source');
                }
            });
        }

        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (!Schema::hasColumn('revenues', 'commission_rule_id')) {
                    $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete()->after('order_id');
                }
                if (!Schema::hasColumn('revenues', 'commission_rule_code')) {
                    $table->string('commission_rule_code', 100)->nullable()->after('commission_rule_id');
                }
                if (!Schema::hasColumn('revenues', 'sale_source')) {
                    $table->string('sale_source', 100)->nullable()->after('commission_rule_code');
                }
                if (!Schema::hasColumn('revenues', 'instructor_percent')) {
                    $table->decimal('instructor_percent', 5, 2)->nullable()->after('platform_fee_amount');
                }
                if (!Schema::hasColumn('revenues', 'platform_percent')) {
                    $table->decimal('platform_percent', 5, 2)->nullable()->after('instructor_percent');
                }
                if (!Schema::hasColumn('revenues', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'commission_rule_id')) {
                    $table->dropForeign(['commission_rule_id']);
                    $table->dropColumn('commission_rule_id');
                }
                if (Schema::hasColumn('orders', 'sale_source')) {
                    $table->dropColumn('sale_source');
                }
            });
        }

        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (Schema::hasColumn('revenues', 'commission_rule_id')) {
                    $table->dropForeign(['commission_rule_id']);
                    $table->dropColumn('commission_rule_id');
                }
                if (Schema::hasColumn('revenues', 'commission_rule_code')) {
                    $table->dropColumn('commission_rule_code');
                }
                if (Schema::hasColumn('revenues', 'sale_source')) {
                    $table->dropColumn('sale_source');
                }
                if (Schema::hasColumn('revenues', 'instructor_percent')) {
                    $table->dropColumn('instructor_percent');
                }
                if (Schema::hasColumn('revenues', 'platform_percent')) {
                    $table->dropColumn('platform_percent');
                }
                if (Schema::hasColumn('revenues', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }
};
