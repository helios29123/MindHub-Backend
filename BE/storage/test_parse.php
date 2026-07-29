<?php
$sqlPath = dirname(__DIR__) . '/../elearning_erd_full_with_notebooklm_video_seed.sql';
$raw = file_get_contents($sqlPath);
$lines = explode("\n", $raw);
$cleanLines = [];
foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
        continue;
    }
    $cleanLines[] = $line;
}
$cleanSql = implode("\n", $cleanLines);
$statements = [];
$current = '';
$inString = false;
$stringChar = '';
$len = strlen($cleanSql);
for ($i = 0; $i < $len; $i++) {
    $char = $cleanSql[$i];
    $prevChar = $i > 0 ? $cleanSql[$i - 1] : '';
    if (($char === "'" || $char === '"') && $prevChar !== '\\') {
        if (!$inString) {
            $inString = true;
            $stringChar = $char;
        } elseif ($char === $stringChar) {
            $inString = false;
        }
    }
    $current .= $char;
    if ($char === ';' && !$inString) {
        $statements[] = trim($current);
        $current = '';
    }
}
for ($i = 40; $i < min(60, count($statements)); $i++) {
    echo "=== STATEMENT $i ===\n" . substr($statements[$i], 0, 150) . "\n\n";
}
