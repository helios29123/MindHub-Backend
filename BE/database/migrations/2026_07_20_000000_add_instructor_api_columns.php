<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payout_accounts')) {
            Schema::table('payout_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('payout_accounts', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('status');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'avatar_url')) {
                    $table->string('avatar_url', 500)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'settings')) {
                    $table->json('settings')->nullable()->after('avatar_url');
                }
            });
        }

        if (Schema::hasTable('instructor_profiles')) {
            Schema::table('instructor_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('instructor_profiles', 'social_links')) {
                    $table->json('social_links')->nullable()->after('level');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payout_accounts')) {
            Schema::table('payout_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('payout_accounts', 'is_default')) {
                    $table->dropColumn('is_default');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'avatar_url')) {
                    $table->dropColumn('avatar_url');
                }
                if (Schema::hasColumn('users', 'settings')) {
                    $table->dropColumn('settings');
                }
            });
        }

        if (Schema::hasTable('instructor_profiles')) {
            Schema::table('instructor_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('instructor_profiles', 'social_links')) {
                    $table->dropColumn('social_links');
                }
            });
        }
    }
};
