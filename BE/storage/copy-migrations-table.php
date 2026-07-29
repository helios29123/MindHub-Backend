<?php
try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create migrations table in mindhub if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS mindhub.migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Clear mindhub migrations
    $conn->exec("TRUNCATE TABLE mindhub.migrations");

    // Copy rows from datn.migrations to mindhub.migrations
    $rows = $conn->query("SELECT migration, batch FROM datn.migrations")->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare("INSERT INTO mindhub.migrations (migration, batch) VALUES (:migration, :batch)");
    foreach ($rows as $row) {
        $stmt->execute([
            ':migration' => $row['migration'],
            ':batch' => $row['batch']
        ]);
    }

    echo "Successfully copied " . count($rows) . " migration records from datn to mindhub.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
