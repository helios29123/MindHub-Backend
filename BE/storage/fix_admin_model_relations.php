<?php

$base = dirname(__DIR__);
$backupDir = $base . '/storage/task-backup-fix-admin-model-relations-' . date('Ymd-His');

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

function backupFile(string $file, string $backupDir): void
{
    if (! file_exists($file)) {
        return;
    }

    $dest = $backupDir . '/' . basename($file);

    copy($file, $dest);
}

function writeFile(string $file, string $content): void
{
    file_put_contents($file, $content);
}

function addModelMethod(string $file, string $methodName, string $methodCode, string $backupDir): void
{
    if (! file_exists($file)) {
        echo "MISSING | {$file}\n";
        return;
    }

    $content = file_get_contents($file);

    if (preg_match('/function\s+' . preg_quote($methodName, '/') . '\s*\(/', $content)) {
        echo "METHOD EXISTS | {$methodName} | {$file}\n";
        return;
    }

    backupFile($file, $backupDir);

    $lastBrace = strrpos($content, '}');

    if ($lastBrace === false) {
        echo "ERROR | Cannot find closing brace | {$file}\n";
        return;
    }

    $newContent =
        rtrim(substr($content, 0, $lastBrace)) .
        "\n\n" .
        trim($methodCode) .
        "\n" .
        substr($content, $lastBrace);

    writeFile($file, $newContent);

    echo "ADDED METHOD | {$methodName} | {$file}\n";
}

/*
|--------------------------------------------------------------------------
| 1. Fix Course::category()
|--------------------------------------------------------------------------
*/

addModelMethod(
    $base . '/app/Models/Course.php',
    'category',
    <<<'CODE'
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }
CODE,
    $backupDir
);

/*
|--------------------------------------------------------------------------
| 2. Fix Order::course()
|--------------------------------------------------------------------------
*/

addModelMethod(
    $base . '/app/Models/Order.php',
    'course',
    <<<'CODE'
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }
CODE,
    $backupDir
);

/*
|--------------------------------------------------------------------------
| 3. Fix AdminAuditLog::admin()
|--------------------------------------------------------------------------
*/

addModelMethod(
    $base . '/app/Models/AdminAuditLog.php',
    'admin',
    <<<'CODE'
    public function admin()
    {
        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'admin_id')
            ? 'admin_id'
            : 'user_id';

        return $this->belongsTo(\App\Models\User::class, $foreignKey);
    }
CODE,
    $backupDir
);

/*
|--------------------------------------------------------------------------
| 4. Create missing CommissionRule model
|--------------------------------------------------------------------------
*/

$commissionRuleFile = $base . '/app/Models/CommissionRule.php';

if (! file_exists($commissionRuleFile)) {
    $code = <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CommissionRule extends Model
{
    protected $table = 'commission_rules';

    protected $guarded = [];

    protected $casts = [
        'platform_rate' => 'float',
        'instructor_rate' => 'float',
        'platform_rate_percent' => 'float',
        'instructor_rate_percent' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
CODE;

    writeFile($commissionRuleFile, $code);

    echo "CREATED | {$commissionRuleFile}\n";
} else {
    echo "EXISTS | {$commissionRuleFile}\n";
}

echo "\nBACKUP_DIR={$backupDir}\n";