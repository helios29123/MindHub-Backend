<?php
try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("DROP DATABASE IF EXISTS mindhub");
    $conn->exec("CREATE DATABASE mindhub");
    $conn->exec("USE mindhub");

    $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
    echo "Current database in PDO: $dbName\n";

    $conn->exec("DROP TABLE IF EXISTS sessions");
    echo "Dropped sessions table if exists.\n";

    $conn->exec("CREATE TABLE sessions (id INT)");
    echo "Created dummy sessions table.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
