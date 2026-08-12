<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Unique Course Statuses</h1>";
try {
    $statuses = \App\Models\Course::select('status')->distinct()->get();
    foreach ($statuses as $s) {
        echo "Status: '" . htmlspecialchars($s->status) . "'<br/>";
    }

    $all = \App\Models\Course::limit(5)->get();
    echo "<h2>Sample Courses</h2>";
    foreach ($all as $c) {
        echo "Title: '{$c->title}', Status: '{$c->status}'<br/>";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
