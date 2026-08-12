<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Clearing Laravel Cache Programmatically</h1>";

function runArtisan($cmd) {
    try {
        \Illuminate\Support\Facades\Artisan::call($cmd);
        $output = \Illuminate\Support\Facades\Artisan::output();
        echo "<h3>Command: Artisan::call('$cmd')</h3>";
        echo "<pre>" . htmlentities($output) . "</pre>";
    } catch (\Exception $e) {
        echo "<h3>Error running '$cmd'</h3>";
        echo "<pre>" . htmlentities($e->getMessage()) . "</pre>";
    }
}

runArtisan("route:clear");
runArtisan("config:clear");
runArtisan("cache:clear");
