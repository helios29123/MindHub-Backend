<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

echo "DB=" . config('database.connections.mysql.database') . PHP_EOL;

$users = [
    ['email' => 'instructor1@mindhub.test', 'name' => 'Instructor Test 1', 'role' => 'instructor'],
    ['email' => 'instructor2@mindhub.test', 'name' => 'Instructor Test 2', 'role' => 'instructor'],
    ['email' => 'learner1@mindhub.test', 'name' => 'Learner Test 1', 'role' => 'learner'],
    ['email' => 'learner2@mindhub.test', 'name' => 'Learner Test 2', 'role' => 'learner'],
];

foreach ($users as $u) {
    $data = [];

    if (Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $u['name'];
    }

    if (Schema::hasColumn('users', 'name')) {
        $data['name'] = $u['name'];
    }

    if (Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $u['email']);
    }

    if (Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    if (Schema::hasColumn('users', 'password')) {
        $data['password'] = Hash::make('password');
    }

    if (Schema::hasColumn('users', 'role')) {
        $data['role'] = $u['role'];
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

    if (Schema::hasColumn('users', 'updated_at')) {
        $data['updated_at'] = now();
    }

    DB::table('users')->updateOrInsert(['email' => $u['email']], $data);

    $row = DB::table('users')->where('email', $u['email'])->first();

    echo $u['email'] . " | id=" . ($row->id ?? 'NULL') . " | role=" . ($row->role ?? 'NULL') . " | status=" . ($row->status ?? 'NULL') . PHP_EOL;
}