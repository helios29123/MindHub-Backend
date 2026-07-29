<?php

$base = dirname(__DIR__);
$backupDir = $base . '/storage/task-backup-fix-order-coupon-' . date('Ymd-His');

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$orderFile = $base . '/app/Models/Order.php';

if (! file_exists($orderFile)) {
    echo "MISSING Order.php\n";
    exit;
}

copy($orderFile, $backupDir . '/Order.php');

$content = file_get_contents($orderFile);

if (preg_match('/function\s+coupon\s*\(/', $content)) {
    echo "METHOD EXISTS | coupon\n";
} else {
    $method = <<<'CODE'

    public function coupon()
    {
        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'coupon_id')
            ? 'coupon_id'
            : (
                \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'discount_code_id')
                    ? 'discount_code_id'
                    : (
                        \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'coupon_code_id')
                            ? 'coupon_code_id'
                            : 'coupon_id'
                    )
            );

        $model = class_exists(\App\Models\Coupon::class)
            ? \App\Models\Coupon::class
            : (
                class_exists(\App\Models\DiscountCode::class)
                    ? \App\Models\DiscountCode::class
                    : \App\Models\Coupon::class
            );

        return $this->belongsTo($model, $foreignKey);
    }
CODE;

    $lastBrace = strrpos($content, '}');

    if ($lastBrace === false) {
        echo "ERROR | Cannot find closing brace\n";
        exit;
    }

    $content =
        rtrim(substr($content, 0, $lastBrace)) .
        "\n" .
        $method .
        "\n" .
        substr($content, $lastBrace);

    file_put_contents($orderFile, $content);

    echo "ADDED | Order::coupon()\n";
}

echo "BACKUP_DIR={$backupDir}\n";