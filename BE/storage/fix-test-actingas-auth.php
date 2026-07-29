<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

echo "DB=" . config('database.connections.mysql.database') . PHP_EOL;

function makeTestUser(string $email): User
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $data = [];

    if (Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $displayName;
    }

    if (Schema::hasColumn('users', 'name')) {
        $data['name'] = $displayName;
    }

    if (Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    if (Schema::hasColumn('users', 'password')) {
        $data['password'] = Hash::make('password');
    }

    if (Schema::hasColumn('users', 'role')) {
        $data['role'] = $role;
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

    DB::table('users')->updateOrInsert(
        ['email' => $email],
        $data
    );

    $user = User::where('email', $email)->first();

    if (!$user) {
        throw new RuntimeException("Cannot create test user {$email}");
    }

    echo "USER={$email} ID={$user->id} ROLE=" . ($user->role ?? 'NULL') . " STATUS=" . ($user->status ?? 'NULL') . PHP_EOL;

    return $user;
}

function replaceFunctionBlock(string $path, string $functionName, string $newCode): void
{
    if (!file_exists($path)) {
        echo "SKIP MISSING FILE: {$path}" . PHP_EOL;
        return;
    }

    $content = file_get_contents($path);
    $pos = strpos($content, 'function ' . $functionName);

    if ($pos === false) {
        echo "FUNCTION NOT FOUND: {$functionName} in {$path}" . PHP_EOL;
        return;
    }

    $braceStart = strpos($content, '{', $pos);
    $depth = 0;
    $end = null;
    $length = strlen($content);

    for ($i = $braceStart; $i < $length; $i++) {
        if ($content[$i] === '{') {
            $depth++;
        } elseif ($content[$i] === '}') {
            $depth--;

            if ($depth === 0) {
                $end = $i + 1;
                break;
            }
        }
    }

    if ($end === null) {
        echo "FUNCTION END NOT FOUND: {$functionName}" . PHP_EOL;
        return;
    }

    $content = substr($content, 0, $pos) . $newCode . substr($content, $end);
    file_put_contents($path, $content);
    echo "PATCHED {$functionName} in {$path}" . PHP_EOL;
}

$commentHelper = <<<'PHP'
function getAuthHeadersForCommentTest(string $email = 'instructor1@mindhub.test'): array
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $data = [];

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $data['name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $data['password'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $data['role'] = $role;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $data['status'] = 'active';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
        $data['is_active'] = 1;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'created_at')) {
        $data['created_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'updated_at')) {
        $data['updated_at'] = now();
    }

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
        ['email' => $email],
        $data
    );

    $user = \App\Models\User::where('email', $email)->first();

    test()->actingAs($user);

    return ['Accept' => 'application/json'];
}
PHP;

$marketingHelper = <<<'PHP'
function getAuthHeadersForMarketingTest(string $email = 'instructor1@mindhub.test'): array
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $data = [];

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $data['name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $data['password'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $data['role'] = $role;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $data['status'] = 'active';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
        $data['is_active'] = 1;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'created_at')) {
        $data['created_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'updated_at')) {
        $data['updated_at'] = now();
    }

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
        ['email' => $email],
        $data
    );

    $user = \App\Models\User::where('email', $email)->first();

    test()->actingAs($user);

    return ['Accept' => 'application/json'];
}
PHP;

replaceFunctionBlock(
    base_path('tests/Feature/InteractionCommentTest.php'),
    'getAuthHeadersForCommentTest',
    $commentHelper
);

replaceFunctionBlock(
    base_path('tests/Feature/MarketingAnnouncementTest.php'),
    'getAuthHeadersForMarketingTest',
    $marketingHelper
);

makeTestUser('instructor1@mindhub.test');
makeTestUser('instructor2@mindhub.test');
makeTestUser('learner1@mindhub.test');

Artisan::call('optimize:clear');
echo Artisan::output();

echo "FIX ACTING AS DONE" . PHP_EOL;