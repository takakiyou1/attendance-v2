<?php
/**
 * Session Manager
 * Centralized session handling with configuration
 */
class Session
{
    private static bool $started = false;
    private static array $config = [];

    /**
     * Set session configuration
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Start session with configuration
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        $config = self::$config;

        // Set session name
        if (isset($config['name'])) {
            session_name($config['name']);
        }

        // Set cookie parameters
        if (isset($config['cookie'])) {
            session_set_cookie_params($config['cookie']);
        }

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;
        }
    }

    /**
     * Set session value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    /**
     * Get current user
     */
    public static function user(): ?array
    {
        return self::get('user');
    }

    /**
     * Set current user
     */
    public static function setUser(array $user): void
    {
        self::set('user', $user);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user');
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    /**
     * Get user ID
     */
    public static function userId(): ?int
    {
        $user = self::user();
        return $user ? (int)($user['id'] ?? 0) : null;
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        self::remove('user');
    }

    /**
     * Regenerate session ID (for security)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}
