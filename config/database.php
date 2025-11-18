<?php
/**
 * Database Configuration (Legacy)
 * Auto-detects local vs production environment
 */

// Detect environment
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', [
    'localhost',
    'localhost:8888',
    '127.0.0.1',
    '127.0.0.1:8888'
]);

if ($isLocalhost) {
    // Local (MAMP)
    $dbHost = 'localhost';
    $dbName = 'attendance_v2';
    $dbUser = 'root';
    $dbPass = 'root';
} else {
    // Production (Xserver)
    $dbHost = 'localhost';
    $dbName = 'yoyoyoyoyoy_retime';
    $dbUser = 'yoyoyoyoyoy_time';
    $dbPass = 'GQca8mk5s';
}

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8",
        $dbUser,
        $dbPass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit;
}
?>
