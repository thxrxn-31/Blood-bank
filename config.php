<?php
// ---- Database settings (XAMPP defaults: user "root", empty password) ----
const DB_HOST = 'localhost';
const DB_NAME = 'bloodbank';
const DB_USER = 'root';
const DB_PASS = '';

session_start();

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('<h2>Database connection failed</h2><p>Check config.php and make sure MySQL is running and the "bloodbank" database is imported.</p>');
}
