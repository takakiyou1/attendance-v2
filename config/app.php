<?php
/**
 * Application Configuration
 */
return [
    // Application name
    'name' => 'Attendance System v2',

    // Application URL base
    'url' => '/attendance-v2',

    // Application environment (development, production)
    'env' => 'development',

    // Debug mode
    'debug' => true,

    // Timezone
    'timezone' => 'Asia/Tokyo',

    // Default locale
    'locale' => 'ja',

    // Path constants
    'paths' => [
        'base' => dirname(__DIR__),
        'app' => dirname(__DIR__) . '/app',
        'core' => dirname(__DIR__) . '/core',
        'config' => dirname(__DIR__) . '/config',
        'public' => dirname(__DIR__) . '/public',
        'views' => dirname(__DIR__) . '/app/Views',
    ],
];
