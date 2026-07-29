<?php
try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("DROP DATABASE IF EXISTS mindhub");
    $conn->exec("CREATE DATABASE mindhub");
    $conn->exec("USE mindhub");

    $sqlPath = dirname(__DIR__) . '/database/sql/elearning_erd_full_with_notebooklm_video_seed.sql';
    if (file_exists($sqlPath)) {
        $sql = file_get_contents($sqlPath);
        $conn->exec($sql);
        echo "Base SQL dump imported successfully into mindhub.\n";
    } else {
        echo "SQL dump file not found at $sqlPath.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
