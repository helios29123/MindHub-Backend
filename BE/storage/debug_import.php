<?php
try {
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

    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("DROP DATABASE IF EXISTS phatnt");
    $conn->exec("CREATE DATABASE phatnt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE phatnt");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

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

    foreach ($statements as $index => $stmt) {
        if ($stmt === '') continue;
        if (preg_match('/SET\s+FOREIGN_KEY_CHECKS/i', $stmt)) {
            continue;
        }
        
        try {
            $conn->exec($stmt);
            echo "Step #$index Succeeded. Current tables: " . implode(', ', $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)) . "\n";
        } catch (Exception $e) {
            echo "Failed at step #$index: " . substr($stmt, 0, 200) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
            break;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
