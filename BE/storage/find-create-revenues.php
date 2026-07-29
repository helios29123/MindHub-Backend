<?php
$lines = file(dirname(__DIR__) . '/database/sql/elearning_erd_full_with_notebooklm_video_seed.sql');
foreach ($lines as $i => $line) {
    if (stripos($line, 'CREATE TABLE') !== false && stripos($line, 'revenues') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
