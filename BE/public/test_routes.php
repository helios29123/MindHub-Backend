<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Laravel Registered Routes</h1>";

$routeCollection = Route::getRoutes();

echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Method</th><th>URI</th><th>Action</th></tr>";
foreach ($routeCollection as $value) {
    echo "<tr>";
    echo "<td>" . implode('|', $value->methods()) . "</td>";
    echo "<td>" . htmlspecialchars($value->uri()) . "</td>";
    echo "<td>" . htmlspecialchars($value->getActionName()) . "</td>";
    echo "</tr>";
}
echo "</table>";
