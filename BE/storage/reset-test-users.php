<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "DB=" . config('database.connections.mysql.database') . PHP_EOL;

$users = [
    ['email' => 'instructor1@mindhub.test', 'name' => 'Instructor Test 1', 'role' => 'instructor'],
    ['email' => 'instructor2@mindhub.test', 'name' => 'Instructor Test 2', 'role' => 'instructor'],
    ['email' => 'learner1@mindhub.test', 'name' => 'Learner Test 1', 'role' => 'learner'],
];

foreach ($users as $item) {
    $data = [
        'full_name' => $item['name'],
        'password_hash' => Hash::make('12345678'),
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('users', 'role')) {
        $data['role'] = $item['role'];
    }

    if (Schema::hasColumn('users', 'status')) {
        $data['status'] = 'active';
    }

    if (Schema::hasColumn('users', 'is_active')) {
        $data['is_active'] = 1;
    }

    if (Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (Schema::hasColumn('users', 'created_at')) {
        $data['created_at'] = now();
    }

    DB::table('users')->updateOrInsert(
        ['email' => $item['email']],
        $data
    );

    $user = User::where('email', $item['email'])->first();

    echo "USER={$item['email']} ";
    echo "ID={$user->id} ";
    echo "ROLE=" . ($user->role ?? 'NULL') . " ";
    echo "STATUS=" . ($user->status ?? 'NULL') . " ";
    echo "HASH_CHECK=" . (Hash::check('password', $user->password) ? 'OK' : 'FAIL') . PHP_EOL;
}

echo "DONE RESET TEST USERS" . PHP_EOL;