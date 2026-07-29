<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sql = file_get_contents(__DIR__ . '/PHATNT_INSTRUCTOR_DEMO_RELATION_FIX.sql');
DB::unprepared($sql);
echo "Successfully executed PHATNT_INSTRUCTOR_DEMO_RELATION_FIX.sql!\n";
