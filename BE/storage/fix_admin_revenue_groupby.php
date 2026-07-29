<?php

$base = dirname(__DIR__);
$file = $base . '/app/Repositories/Admin/AdminRevenueRepository.php';
$backupDir = $base . '/storage/task-backup-fix-admin-revenue-groupby-' . date('Ymd-His');

if (! is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

if (! file_exists($file)) {
    echo "MISSING AdminRevenueRepository.php\n";
    exit(1);
}

copy($file, $backupDir . '/AdminRevenueRepository.php');

$content = file_get_contents($file);

function replaceMethod(string $content, string $methodName, string $newMethod): string
{
    $pattern = '/public\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)[^{]*\{/m';

    if (! preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        echo "METHOD_NOT_FOUND | {$methodName}\n";
        return $content;
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
public function sourceBreakdown(array $filters = [])
{
    $query = \App\Models\Revenue::query()
        ->selectRaw('COALESCE(sale_channel, "unknown") as sale_channel')
        ->selectRaw('SUM(gross_amount) as gross_amount')
        ->selectRaw('SUM(instructor_amount) as instructor_amount')
        ->selectRaw('SUM(platform_fee_amount) as platform_fee_amount')
        ->selectRaw('COUNT(*) as total');

    if (!empty($filters['from_date'])) {
        $query->whereDate('earned_at', '>=', $filters['from_date']);
    }

    if (!empty($filters['to_date'])) {
        $query->whereDate('earned_at', '<=', $filters['to_date']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('earned_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('earned_at', '<=', $filters['date_to']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query
        ->groupByRaw('COALESCE(sale_channel, "unknown")')
        ->orderByRaw('gross_amount DESC')
        ->get();
}
CODE;

$chartMethod = <<<'CODE'
public function chart(array $filters = [])
{
    $query = \App\Models\Revenue::query()
        ->selectRaw('DATE_FORMAT(earned_at, "%Y-%m") as month')
        ->selectRaw('SUM(instructor_amount) as instructor_amount')
        ->selectRaw('SUM(platform_fee_amount) as platform_fee_amount')
        ->selectRaw('SUM(gross_amount) as gross_amount')
        ->selectRaw('COUNT(*) as total');

    if (!empty($filters['from_date'])) {
        $query->whereDate('earned_at', '>=', $filters['from_date']);
    }

    if (!empty($filters['to_date'])) {
        $query->whereDate('earned_at', '<=', $filters['to_date']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('earned_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('earned_at', '<=', $filters['date_to']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query
        ->groupByRaw('DATE_FORMAT(earned_at, "%Y-%m")')
        ->orderBy('month', 'asc')
        ->get();
}
CODE;

$content = replaceMethod($content, 'sourceBreakdown', $sourceBreakdownMethod);
$content = replaceMethod($content, 'chart', $chartMethod);

file_put_contents($file, $content);

echo "BACKUP_DIR={$backupDir}\n";