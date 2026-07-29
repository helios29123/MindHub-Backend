<?php
try {
    $sqlPath = dirname(__DIR__) . '/database/sql/elearning_erd_full_with_notebooklm_video_seed.sql';
    if (!file_exists($sqlPath)) {
        die("SQL dump file not found at $sqlPath.\n");
    }

    $raw = file_get_contents($sqlPath);
    $bom = substr($raw, 0, 2);
    
    $utf8Sql = $raw;
    if ($bom === "\xFF\xFE") {
        echo "Detected UTF-16 LE encoding. Converting to UTF-8...\n";
        $utf8Sql = mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');
    } elseif ($bom === "\xFE\xFF") {
        echo "Detected UTF-16 BE encoding. Converting to UTF-8...\n";
        $utf8Sql = mb_convert_encoding($raw, 'UTF-8', 'UTF-16BE');
    } else {
        echo "Detected standard/UTF-8 encoding.\n";
    }

    // Connect to MySQL
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Rebuild mindhub database
    $conn->exec("DROP DATABASE IF EXISTS mindhub");
    $conn->exec("CREATE DATABASE mindhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE mindhub");

    // Execute SQL
    $conn->exec($utf8Sql);
    echo "SQL dump imported successfully into mindhub.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
