<?php
$lines = file(dirname(__DIR__) . '/database/sql/elearning_erd_full_with_notebooklm_video_seed.sql');
foreach ($lines as $i => $line) {
    if (preg_match('/CREATE TABLE\s+\w+\./i', $line)) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
