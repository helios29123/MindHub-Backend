<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Creating migrations table if not exists...\n";
    Schema::create('migrations', function ($table) {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });
} catch (Exception $e) {
    echo "Migrations table already exists or created: " . $e->getMessage() . "\n";
}

$baseMigrations = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_06_06_160042_create_personal_access_tokens_table',
    '2026_06_08_000000_import_base_schema',
    '2026_06_09_053637_create_cat_users_table',
    '2026_06_09_053638_create_cat_categories_table',
    '2026_06_09_053638_create_cat_courses_table',
    '2026_06_09_053639_create_cat_coupons_table',
    '2026_06_09_053639_create_cat_orders_table',
    '2026_06_09_053640_create_cat_course_reviews_table',
    '2026_06_09_053640_create_cat_enrollments_table',
    '2026_06_09_053641_create_cat_banners_table',
    '2026_06_09_053641_create_cat_instructor_profiles_table',
    '2026_06_09_053642_create_cat_course_categories_table',
    '2026_07_11_000001_create_course_content_tables',
    '2026_07_11_000002_create_revenues_table_if_missing',
];

echo "Inserting base migration records...\n";
foreach ($baseMigrations as $migration) {
    DB::table('migrations')->updateOrInsert(
        ['migration' => $migration],
        ['batch' => 1]
    );
}

echo "Base migration records successfully updated.\n";
