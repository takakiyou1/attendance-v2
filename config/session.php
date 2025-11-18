<?php
/**
 * Session Configuration
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
        'name' => 'attendance_session',
        'cookie' => [
            'path' => '/attendance-v2',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ],
        'lifetime' => 7200,
        'cookie_lifetime' => 0,
    ];
} else {
    // Production (Xserver)
    return [
        'name' => 'attendance_session',
        'cookie' => [
            'path' => '/',
            'domain' => 'retimeshift.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ],
        'lifetime' => 7200,
        'cookie_lifetime' => 0,
    ];
}
