<?php
try {
    $sqlPath = dirname(__DIR__) . '/database/sql/elearning_erd_full_with_notebooklm_video_seed.sql';
    if (!file_exists($sqlPath)) {
        die("SQL dump file not found at $sqlPath.\n");
    }

    $raw = file_get_contents($sqlPath);
    
    // Clean SQL lines
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

    // Connect to MySQL
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Rebuild mindhub database
    $conn->exec("DROP DATABASE IF EXISTS mindhub");
    $conn->exec("CREATE DATABASE mindhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE mindhub");

    // Split clean SQL by statement (basic parser that respects quotes)
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
    if (trim($current) !== '') {
        $statements[] = trim($current);
    }

    echo "Total statements to execute: " . count($statements) . "\n";

    foreach ($statements as $index => $stmt) {
        if ($stmt === '') continue;
        try {
            $conn->exec($stmt);
        } catch (Exception $e) {
            echo "Failed at statement #$index:\n";
            echo substr($stmt, 0, 500) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    echo "All statements executed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
