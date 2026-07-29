<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlFile = __DIR__ . '/../../elearning_erd_full_with_notebooklm_video_seed.sql';
if (!file_exists($sqlFile)) {
    echo "SQL File not found at: " . $sqlFile . "\n";
    exit(1);
}

echo "Reading SQL file...\n";
$sql = file_get_contents($sqlFile);
echo "Executing SQL statements...\n";
DB::unprepared($sql);
echo "Import complete!\n";
