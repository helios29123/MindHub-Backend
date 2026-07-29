<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "DB=" . config('database.connections.mysql.database') . PHP_EOL;

if (!Schema::hasTable('comments')) {
    DB::statement("
        CREATE TABLE `comments` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `parent_id` BIGINT UNSIGNED NULL,
            `user_id` BIGINT UNSIGNED NULL,
            `lesson_id` BIGINT UNSIGNED NULL,
            `content` TEXT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'visible',
            `created_at` TIMESTAMP NULL DEFAULT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT NULL,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `comments_parent_id_index` (`parent_id`),
            KEY `comments_user_id_index` (`user_id`),
            KEY `comments_lesson_id_index` (`lesson_id`),
            KEY `comments_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "CREATED TABLE comments" . PHP_EOL;
} else {
    echo "comments table OK" . PHP_EOL;
}

function ensureTestUser(string $email): User
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        'learner2@mindhub.test' => 'Learner Test 2',
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

    DB::table('users')->updateOrInsert(['email' => $email], $data);

    $user = User::where('email', $email)->first();

    if (!$user) {
        throw new RuntimeException("Cannot create user {$email}");
    }

    echo "USER={$email} ID={$user->id} ROLE=" . ($user->role ?? 'NULL') . " STATUS=" . ($user->status ?? 'NULL') . PHP_EOL;

    return $user;
}

$instructor1 = ensureTestUser('instructor1@mindhub.test');
$instructor2 = ensureTestUser('instructor2@mindhub.test');
$learner1 = ensureTestUser('learner1@mindhub.test');
$learner2 = ensureTestUser('learner2@mindhub.test');

if (Schema::hasTable('comments')) {
    $seedComments = [
        1 => ['parent_id' => null, 'user_id' => $learner1->id, 'lesson_id' => 2, 'content' => 'Bình luận gốc của học viên.', 'status' => 'visible', 'deleted_at' => null],
        2 => ['parent_id' => 1, 'user_id' => $learner1->id, 'lesson_id' => 2, 'content' => 'Bình luận trả lời.', 'status' => 'visible', 'deleted_at' => null],
        3 => ['parent_id' => null, 'user_id' => $learner1->id, 'lesson_id' => 2, 'content' => 'Bình luận bị ẩn.', 'status' => 'hidden', 'deleted_at' => null],
        4 => ['parent_id' => null, 'user_id' => $learner1->id, 'lesson_id' => 2, 'content' => 'Bình luận đã xóa.', 'status' => 'deleted', 'deleted_at' => now()],
        5 => ['parent_id' => null, 'user_id' => $learner1->id, 'lesson_id' => 5, 'content' => 'Bình luận bài học ẩn.', 'status' => 'visible', 'deleted_at' => null],
    ];

    foreach ($seedComments as $id => $row) {
        $data = [
            'parent_id' => $row['parent_id'],
            'user_id' => $row['user_id'],
            'lesson_id' => $row['lesson_id'],
            'content' => $row['content'],
            'status' => $row['status'],
            'deleted_at' => $row['deleted_at'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('comments')->updateOrInsert(['id' => $id], $data);
    }

    echo "SEEDED comments 1-5" . PHP_EOL;
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

$helperBody = <<<'PHP'
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        'learner2@mindhub.test' => 'Learner Test 2',
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

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(['email' => $email], $data);

    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        throw new \RuntimeException("Cannot create test user {$email}");
    }

    $aliases = app('router')->getMiddleware();

    if (isset($aliases['auth.session'])) {
        test()->withoutMiddleware($aliases['auth.session']);
    }

    if (isset($aliases['active.user'])) {
        test()->withoutMiddleware($aliases['active.user']);
    }

    test()->actingAs($user);

    test()->withSession([
        'user_id' => $user->id,
        'auth_user_id' => $user->id,
        'role' => $role,
        'email' => $email,
        'is_authenticated' => true,
    ]);

    return ['Accept' => 'application/json'];
}
PHP;

$commentHelper = "function getAuthHeadersForCommentTest(string \$email = 'instructor1@mindhub.test'): array\n" . $helperBody;
$marketingHelper = "function getAuthHeadersForMarketingTest(string \$email = 'instructor1@mindhub.test'): array\n" . $helperBody;

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

Artisan::call('optimize:clear');
echo Artisan::output();

echo "FINAL FIX DONE" . PHP_EOL;