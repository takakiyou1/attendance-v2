<?php
/**
 * Database Configuration
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
    return [
        'host' => 'localhost',
        'database' => 'attendance_v2',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];
} else {
    // Production (Xserver)
    return [
        'host' => 'localhost',
        'database' => 'yoyoyoyoyoy_retime',
        'username' => 'yoyoyoyoyoy_time',
        'password' => 'GQca8mk5s',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];
}
