<?php

$base = dirname(__DIR__);
$backupDir = $base . '/storage/task-backup-fix-order-revenue-' . date('Ymd-His');

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$orderFile = $base . '/app/Models/Order.php';

if (! file_exists($orderFile)) {
    echo "MISSING Order.php\n";
    exit(1);
}

copy($orderFile, $backupDir . '/Order.php');

$content = file_get_contents($orderFile);

if (preg_match('/function\s+revenue\s*\(/', $content)) {
    echo "METHOD EXISTS | revenue\n";
} else {
    $method = <<<'CODE'

    public function revenue()
    {
        return $this->hasOne(\App\Models\Revenue::class, 'order_id');
    }
CODE;

    $lastBrace = strrpos($content, '}');

    if ($lastBrace === false) {
        echo "ERROR | Cannot find closing brace\n";
        exit(1);
    }

    $content =
        rtrim(substr($content, 0, $lastBrace)) .
        "\n" .
        $method .
        "\n" .
        substr($content, $lastBrace);

    file_put_contents($orderFile, $content);

    echo "ADDED | Order::revenue()\n";
}

echo "BACKUP_DIR={$backupDir}\n";