<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Fixing sessions table column nullability...\n";

DB::statement("ALTER TABLE `sessions` MODIFY `payload` LONGTEXT NULL;");
DB::statement("ALTER TABLE `sessions` MODIFY `last_activity` INT NULL;");

echo "Sessions table column nullability updated successfully!\n";
