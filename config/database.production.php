<?php
/**
 * Production Database Configuration
 * For Xserver deployment
 */

$host = 'mysql6001.xserver.jp';
$dbname = 'yoyoyoyoyoy_retime';
$user = 'yoyoyoyoyoy_time';
$pass = 'GQca8mk5s';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
