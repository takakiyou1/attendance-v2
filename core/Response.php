<?php
/**
 * HTTP Response Helper
 * Handles JSON and redirect responses
 */
class Response
{
    /**
     * Send JSON response
     */
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success JSON response
     */
    public static function success($data = [], string $message = 'Success'): void
    {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Send error JSON response
     */
    public static function error(string $message, int $statusCode = 400, $errors = null): void
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        self::json($response, $statusCode);
    }

    /**
     * Redirect to URL
     */
    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Redirect back
     */
    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/attendance-v2/public/index.php';
        self::redirect($referer);
    }

    /**
     * Send 404 response
     */
    public static function notFound(string $message = 'Page not found'): void
    {
        http_response_code(404);
        echo "<h1>404 - $message</h1>";
        exit;
    }

    /**
     * Send 403 Forbidden response
     */
    public static function forbidden(string $message = 'Access denied'): void
    {
        http_response_code(403);
        echo "<h1>403 - $message</h1>";
        exit;
    }

    /**
     * Set HTTP status code
     */
    public static function setStatusCode(int $code): void
    {
        http_response_code($code);
    }
}
