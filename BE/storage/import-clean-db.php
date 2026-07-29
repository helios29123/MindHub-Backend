<?php
try {
    echo "Connecting to database...\n";
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Dropping database phatnt if exists...\n";
    $conn->exec("DROP DATABASE IF EXISTS phatnt");

    echo "Creating database phatnt...\n";
    $conn->exec("CREATE DATABASE phatnt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE phatnt");

    $sqlPath = dirname(__DIR__) . '/../elearning_erd_full_with_notebooklm_video_seed.sql';
    echo "Reading SQL file: $sqlPath\n";
    $raw = file_get_contents($sqlPath);

    // Split SQL by statements
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

    echo "Total statements found: " . count($statements) . "\n";
    echo "Executing statements with FOREIGN_KEY_CHECKS = 0...\n";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    foreach ($statements as $index => $stmt) {
        if (empty($stmt)) continue;
        // Skip explicitly setting foreign key checks inside the statements to avoid turning it back on mid-import
        if (stripos($stmt, 'FOREIGN_KEY_CHECKS') !== false) {
            continue;
        }
        try {
            $conn->exec($stmt);
        } catch (Exception $e) {
            echo "Failed at statement #$index: " . substr($stmt, 0, 100) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Database import from SQL file completed successfully.\n";

} catch (Exception $e) {
    echo "Error during database restoration: " . $e->getMessage() . "\n";
    exit(1);
}
