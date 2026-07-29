<?php
$sqlPath = dirname(__DIR__) . '/../elearning_erd_full_with_notebooklm_video_seed.sql';
$lines = file($sqlPath);
foreach ($lines as $i => $line) {
    if (stripos($line, 'INSERT INTO lessons') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
        for ($j = 1; $j <= 20; $j++) {
            if (isset($lines[$i + $j])) {
                $content = trim($lines[$i + $j]);
                if (str_starts_with($content, '(5,') || str_contains($content, ' (5,')) {
                    echo "  " . $content . "\n";
                }
            }
        }
    }
}
