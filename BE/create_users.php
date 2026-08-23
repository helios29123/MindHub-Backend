<?php

require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$learner = User::updateOrCreate(
    ['email' => 'learner@mindhub.com'],
    [
        'full_name' => 'Demo Learner',
        'password_hash' => Hash::make('password123'),
        'role' => 'learner',
        'status' => 'active',
        'email_verified_at' => now(),
    ]
);

$instructor = User::updateOrCreate(
    ['email' => 'instructor@mindhub.com'],
    [
        'full_name' => 'Demo Instructor',
        'password_hash' => Hash::make('password123'),
        'role' => 'instructor',
        'status' => 'active',
        'email_verified_at' => now(),
    ]
);

echo "Learner created: " . $learner->email . "\n";
echo "Instructor created: " . $instructor->email . "\n";
