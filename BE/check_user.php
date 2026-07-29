<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'instructor1@mindhub.test')->first();

if (!$user) {
    echo "USER_NOT_FOUND: instructor1@mindhub.test does not exist in users table!\n";
    $allUsers = User::select('id', 'email', 'role', 'status')->get();
    echo "Existing users in database:\n";
    print_r($allUsers->toArray());
} else {
    echo "USER_FOUND:\n";
    echo "ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Status: {$user->status}\n";
    
    $passwordColumn = isset($user->password_hash) && $user->password_hash ? 'password_hash' : (isset($user->password) ? 'password' : 'unknown');
    $currentHash = $user->password_hash ?? $user->password ?? '';
    $isMatch = Hash::check('12345678', $currentHash);

    echo "Password Column: {$passwordColumn}\n";
    echo "Hash Check for '12345678': " . ($isMatch ? "MATCH (TRUE)" : "NO MATCH (FALSE)") . "\n";
}
