<?php
/**
 * Database Connection
 * Returns PDO instance for database queries
 */

$DB_HOST = 'itschaddotnet.a2hosted.com';
$DB_NAME = 'itschadd_bank_news_prototype';
$DB_USER = 'itschadd_nfr_proto';
$DB_PASS = 'nfr_proto26';
$DB_PORT = 3306;

try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
