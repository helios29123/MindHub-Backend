<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

$email = 'admin@mindhub.test';
$password = '12345678';

echo "=== USERS COLUMNS ===\n";
print_r(Schema::getColumnListing('users'));

echo "\n=== ADMIN USERS BEFORE ===\n";
$admins = DB::table('users')
    ->where('role', 'admin')
    ->select('id', 'email', 'role', 'status')
    ->get();

echo json_encode($admins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$user = DB::table('users')->where('email', $email)->first();

if (! $user) {
    echo "\nADMIN_NOT_FOUND | Creating admin: {$email}\n";

    $data = [
        'email' => $email,
        'role' => 'admin',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = 'Admin MindHub';
    }

    if (Schema::hasColumn('users', 'name')) {
        $data['name'] = 'Admin MindHub';
    }

    if (Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (Schema::hasColumn('users', 'password')) {
        $data['password'] = Hash::make($password);
    } elseif (Schema::hasColumn('users', 'password_hash')) {
        $data['password_hash'] = Hash::make($password);
    } else {
        echo "ERROR | users table does not have password/password_hash column.\n";
        exit(1);
    }

    DB::table('users')->insert($data);
} else {
    echo "\nADMIN_FOUND | Resetting admin: {$email}\n";

    $data = [
        'role' => 'admin',
        'status' => 'active',
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (Schema::hasColumn('users', 'password')) {
        $data['password'] = Hash::make($password);
    } elseif (Schema::hasColumn('users', 'password_hash')) {
        $data['password_hash'] = Hash::make($password);
    } else {
        echo "ERROR | users table does not have password/password_hash column.\n";
        exit(1);
    }

    DB::table('users')->where('email', $email)->update($data);
}

echo "\n=== ADMIN USERS AFTER ===\n";
$admins = DB::table('users')
    ->where('role', 'admin')
    ->select('id', 'email', 'role', 'status')
    ->get();

echo json_encode($admins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\nRESET_ADMIN_OK | email={$email} | password={$password}\n";