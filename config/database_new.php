<?php
/**
 * Database Configuration
 */
return [
    'host' => 'localhost',
    'database' => 'attendance_v2',
    'username' => 'root',
    'password' => 'root',  // Change for XAMPP: ''
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
