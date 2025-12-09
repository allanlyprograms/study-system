<?php
// Edit these variables for your environment
$DB_HOST = '10.121.1.114';
$DB_PORT = '5432';
$DB_NAME = 'temp_postgres';
$DB_USER = 'postgres';
$DB_PASS = 'postgres';

try {
    $dsn = "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    echo "DB connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
