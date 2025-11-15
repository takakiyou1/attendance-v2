<?php
/**
 * Session Configuration
 */
return [
    // Session name
    'name' => 'attendance_session',

    // Cookie parameters
    'cookie' => [
        'path' => '/attendance-v2',
        'domain' => 'localhost',
        'secure' => false,       // Set to true for HTTPS
        'httponly' => true,
        'samesite' => 'None'
    ],

    // Session lifetime (in seconds)
    'lifetime' => 7200,  // 2 hours

    // Session cookie lifetime (0 = until browser closes)
    'cookie_lifetime' => 0,
];
