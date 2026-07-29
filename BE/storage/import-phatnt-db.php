<?php
try {
    $sqlPath = dirname(__DIR__) . '/../elearning_erd_full_with_notebooklm_video_seed.sql';
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

    // Rebuild phatnt database
    $conn->exec("DROP DATABASE IF EXISTS phatnt");
    $conn->exec("CREATE DATABASE phatnt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE phatnt");

    // Disable foreign key checks for the connection
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

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

    echo "Total statements parsed: " . count($statements) . "\n";

    $conn->beginTransaction();
    foreach ($statements as $index => $stmt) {
        if ($stmt === '') continue;
        
        // Skip setting foreign key checks inside the statements to prevent overriding our connection setting
        if (preg_match('/SET\s+FOREIGN_KEY_CHECKS/i', $stmt)) {
            echo "Skipping setting FK checks statement #$index: $stmt\n";
            continue;
        }
        
        try {
            $conn->exec($stmt);
        } catch (Exception $e) {
            echo "Failed at statement #$index:\n";
            echo substr($stmt, 0, 500) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
            $conn->rollBack();
            exit(1);
        }
    }
    $conn->commit();

    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "All statements executed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
