<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

try {
    $authService = app(AuthService::class);
    $req = Request::create('/api/auth/login', 'POST');
    $result = $authService->login([
        'email' => 'instructor1@mindhub.test',
        'password' => '12345678',
    ], $req);

    echo "SUCCESS!\n";
    print_r($result);
} catch (\Throwable $e) {
    echo "EXCEPTIONS: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
