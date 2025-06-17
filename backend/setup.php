<?php
// backend/setup.php
// Run this script ONCE to create the database and import schema automatically.

$config = require __DIR__ . '/config/config.php';
$adminUser = 'root'; // Change if needed
$adminPass = '';
$schemaFile = __DIR__ . '/database/schema.sql';
$dbName = $config['dbname'];

try {
    // Connect to MySQL as admin (no database selected)
    $pdo = new PDO("mysql:host={$config['host']}", $adminUser, $adminPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database '$dbName' created or already exists.\n";

    // Import schema
    $pdo->exec("USE `$dbName`;");
    $schema = file_get_contents($schemaFile);
    $pdo->exec($schema);
    echo "Schema imported successfully.\n";

    echo "Setup complete!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
