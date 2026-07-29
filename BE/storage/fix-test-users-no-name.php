<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

echo "DB=" . config('database.connections.mysql.database') . PHP_EOL;

echo "USERS COLUMNS:" . PHP_EOL;
foreach (Schema::getColumnListing('users') as $c) {
    echo "- {$c}" . PHP_EOL;
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
    if ($braceStart === false) {
        echo "BRACE NOT FOUND: {$functionName}" . PHP_EOL;
        return;
    }

    $depth = 0;
    $end = null;
    $length = strlen($content);

    for ($i = $braceStart; $i < $length; $i++) {
        $char = $content[$i];

        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
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

$commonHelperBody = <<<'PHP'
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $userData = [];

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $userData['name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $userData['full_name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $userData['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $userData['phone'] = '0900000000';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $userData['password'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $userData['role'] = $role;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $userData['status'] = 'active';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
        $userData['is_active'] = 1;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
        $userData['email_verified_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'updated_at')) {
        $userData['updated_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'created_at')) {
        $userData['created_at'] = now();
    }

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
        ['email' => $email],
        $userData
    );

    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        throw new \RuntimeException("Cannot create test user: {$email}");
    }

    if (
        \Illuminate\Support\Facades\Schema::hasColumn('users', 'password')
        && !\Illuminate\Support\Facades\Hash::check('password', $user->password)
    ) {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
    }

    $response = test()->postJson('/api/auth/login', [
        'email' => $email,
        'password' => 'password',
    ]);

    $response->assertSuccessful();

    $json = $response->json();

    $token = data_get($json, 'data.token')
        ?? data_get($json, 'token')
        ?? data_get($json, 'data.access_token')
        ?? data_get($json, 'access_token');

    $headers = ['Accept' => 'application/json'];

    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    return $headers;
}
PHP;

$commentHelper = "function getAuthHeadersForCommentTest(string \$email = 'instructor1@mindhub.test'): array\n" . $commonHelperBody;
$marketingHelper = "function getAuthHeadersForMarketingTest(string \$email = 'instructor1@mindhub.test'): array\n" . $commonHelperBody;

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

$testUsers = [
    'instructor1@mindhub.test',
    'instructor2@mindhub.test',
    'learner1@mindhub.test',
];

foreach ($testUsers as $email) {
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $data = [];

    if (Schema::hasColumn('users', 'name')) {
        $data['name'] = $displayName;
    }

    if (Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $displayName;
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

    if (Schema::hasColumn('users', 'updated_at')) {
        $data['updated_at'] = now();
    }

    if (Schema::hasColumn('users', 'created_at')) {
        $data['created_at'] = now();
    }

    DB::table('users')->updateOrInsert(['email' => $email], $data);

    $row = DB::table('users')->where('email', $email)->first();

    echo "USER={$email}";
    echo " ID=" . ($row->id ?? 'NULL');
    echo " ROLE=" . ($row->role ?? 'NULL');
    echo " STATUS=" . ($row->status ?? 'NULL');

    if (Schema::hasColumn('users', 'password')) {
        echo " HASH_CHECK=" . (Hash::check('password', $row->password) ? 'OK' : 'FAIL');
    }

    echo PHP_EOL;
}

Artisan::call('optimize:clear');
echo Artisan::output();

echo "FIX TEST USERS DONE" . PHP_EOL;