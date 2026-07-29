<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

$user = User::where('email', 'instructor1@mindhub.test')->first();
Auth::login($user);

function testEndpoint($uri) {
    echo "=== API: $uri ===\n";
    $req = Request::create($uri, 'GET');
    $req->headers->set('Accept', 'application/json');
    $response = app()->handle($req);
    echo $response->getContent() . "\n\n";
}

testEndpoint('/api/instructor/dashboard/top-courses?limit=5');
testEndpoint('/api/instructor/revenues/top-courses?limit=5');
