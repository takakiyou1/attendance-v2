<?php
class Router {
    private $routes = [];

    public function get($path, $action) {
        $this->routes['GET'][$path] = $action;
    }

    public function post($path, $action) {
        $this->routes['POST'][$path] = $action;
    }

    public function run() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // ✅ /attendance-v2/public/index.php を削除
        $uri = str_replace('/attendance-v2/public/index.php', '', $uri);

        // ✅ /index.php を削除（念のため）
        $uri = str_replace('/index.php', '', $uri);

        // ✅ /attendance-v2/public を削除
        $uri = str_replace('/attendance-v2/public', '', $uri);

        // ✅ 末尾スラッシュや空を整形
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        // 🔍 デバッグ出力
        error_log("=== Router Debug ===");
        error_log("Original REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NULL'));
        error_log("Parsed URI: {$uri}");
        error_log("Method: {$method}");
        error_log("Available routes for {$method}: " . json_encode($this->routes[$method] ?? []));
        error_log("Route exists? " . (isset($this->routes[$method][$uri]) ? 'YES' : 'NO'));

        if (isset($this->routes[$method][$uri])) {
            [$controller, $methodName] = explode('@', $this->routes[$method][$uri]);
            $controllerPath = __DIR__ . '/../app/controllers/' . $controller . '.php';

            if (file_exists($controllerPath)) {
                require_once $controllerPath;
                $instance = new $controller();
                $instance->$methodName();
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found: {$uri}";
    }
}
