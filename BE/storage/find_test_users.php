<?php

use Illuminate\Support\Facades\DB;

$users = DB::table('users')
    ->select('id', 'full_name', 'email', 'role', 'status', 'locked')
    ->whereIn('role', ['admin', 'instructor', 'learner'])
    ->orderBy('role')
    ->orderBy('id')
    ->limit(50)
    ->get();

foreach ($users as $user) {
    echo $user->id . " | " . $user->role . " | " . $user->email . " | " . $user->status . " | locked=" . $user->locked . PHP_EOL;
}
