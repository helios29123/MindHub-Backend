<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$emails = ['admin@mindhub.test', 'instructor1@mindhub.test', 'learner1@mindhub.test', 'instructor1@mindhub.local'];

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $check = Hash::check('12345678', $user->password_hash);
        echo "User {$user->email} (role: {$user->role}, status: {$user->status}): Password '12345678' match = " . ($check ? 'YES' : 'NO') . "\n";
    } else {
        echo "User {$email} NOT FOUND in DB\n";
    }
}
