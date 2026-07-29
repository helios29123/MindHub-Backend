<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Notifications Columns: " . implode(', ', Schema::getColumnListing('notifications')) . "\n";
print_r(DB::table('notifications')->limit(3)->get()->toArray());
