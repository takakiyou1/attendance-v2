<?php
/**
 * Environment Configuration
 *
 * Automatically detects local vs production environment
 * and sets appropriate URL paths
 */

// Detect environment based on hostname
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', [
    'localhost',
    'localhost:8888',
    '127.0.0.1',
    '127.0.0.1:8888'
]);

// Set environment constants
define('IS_PRODUCTION', !$isLocalhost);
define('ENVIRONMENT', IS_PRODUCTION ? 'production' : 'local');

// Base URL for routing (index.php path)
if (IS_PRODUCTION) {
    define('BASE_URL', '');  // Production: https://retimeshift.com/login
} else {
    define('BASE_URL', '/attendance-v2/public/index.php');  // Local: http://localhost:8888/attendance-v2/public/index.php/login
}

// CSS and static assets URL
if (IS_PRODUCTION) {
    define('ASSET_URL', '');  // Production: /css/style.css
} else {
    define('ASSET_URL', '/attendance-v2/public');  // Local: /attendance-v2/public/css/style.css
}

/**
 * Helper function to generate URLs
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

/**
 * Helper function to generate asset URLs
 */
function asset($path) {
    $path = ltrim($path, '/');
    return ASSET_URL . '/' . $path;
}
