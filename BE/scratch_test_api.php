<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

$user = User::where('email', 'instructor1@mindhub.test')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

Auth::login($user);
echo "Logged in as: " . Auth::user()->full_name . " (ID: " . Auth::id() . ")\n\n";

function testEndpoint($uri) {
    echo "=== API: $uri ===\n";
    $req = Request::create($uri, 'GET');
    $req->headers->set('Accept', 'application/json');
    $response = app()->handle($req);
    echo $response->getContent() . "\n\n";
}

testEndpoint('/api/instructor/dashboard');
testEndpoint('/api/instructor/courses');
testEndpoint('/api/instructor/dashboard/top-courses');
testEndpoint('/api/instructor/dashboard/enrollment-chart');
