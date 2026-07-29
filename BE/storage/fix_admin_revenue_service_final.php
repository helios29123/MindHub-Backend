<?php

$base = dirname(__DIR__);
$file = $base . '/app/Services/Admin/AdminRevenueService.php';
$backupDir = $base . '/storage/task-backup-fix-admin-revenue-service-final-' . date('Ymd-His');

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

if (! file_exists($file)) {
    echo "MISSING AdminRevenueService.php\n";
    exit(1);
}

copy($file, $backupDir . '/AdminRevenueService.php');

$content = file_get_contents($file);

function replaceMethod(string $content, string $methodName, string $newMethod): string
{
    $pattern = '/public\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)\s*:\s*array\s*\{/m';

    if (! preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        $pattern = '/public\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)[^{]*\{/m';

        if (! preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
            echo "METHOD_NOT_FOUND | {$methodName}\n";
            return $content;
        }
    }

    $start = $match[0][1];
    $bracePos = strpos($content, '{', $start);

    if ($bracePos === false) {
        echo "OPEN_BRACE_NOT_FOUND | {$methodName}\n";
        return $content;
    }

    $depth = 0;
    $len = strlen($content);
    $end = null;

    for ($i = $bracePos; $i < $len; $i++) {
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
        echo "CLOSE_BRACE_NOT_FOUND | {$methodName}\n";
        return $content;
    }

    echo "REPLACED | {$methodName}\n";

    return substr($content, 0, $start)
        . rtrim($newMethod)
        . "\n\n"
        . substr($content, $end);
}

$sourceBreakdownMethod = <<<'CODE'
public function sourceBreakdown(array $filters = []): array
{
    return $this->repository
        ->sourceBreakdown($filters)
        ->map(function ($item): array {
            return [
                'sale_channel' => $item->sale_channel ?? 'unknown',
                'gross_amount' => (float) ($item->gross_amount ?? 0),
                'instructor_amount' => (float) ($item->instructor_amount ?? 0),
                'platform_fee_amount' => (float) ($item->platform_fee_amount ?? 0),
                'total' => (int) ($item->total ?? 0),
            ];
        })
        ->values()
        ->toArray();
}
CODE;

$chartMethod = <<<'CODE'
public function chart(array $filters = []): array
{
    return $this->repository
        ->chart($filters)
        ->map(function ($item): array {
            return [
                'month' => $item->month,
                'gross_amount' => (float) ($item->gross_amount ?? 0),
                'instructor_amount' => (float) ($item->instructor_amount ?? 0),
                'platform_fee_amount' => (float) ($item->platform_fee_amount ?? 0),
                'total' => (int) ($item->total ?? 0),
            ];
        })
        ->values()
        ->toArray();
}
CODE;

$content = replaceMethod($content, 'sourceBreakdown', $sourceBreakdownMethod);
$content = replaceMethod($content, 'chart', $chartMethod);

file_put_contents($file, $content);

echo "BACKUP_DIR={$backupDir}\n";