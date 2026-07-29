<?php
$sqlPath = dirname(__DIR__) . '/../elearning_erd_full_with_notebooklm_video_seed.sql';
$lines = file($sqlPath);
foreach ($lines as $i => $line) {
    if (stripos($line, 'mindhub.test') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
