<?php
// ============================================================
// ROUTER — app/Core/Router.php
// ============================================================
// Nhận method + URI → tìm route phù hợp → chạy middleware
// → gọi Controller::method().
//
// TODO (TV1 — Ngày 1): Hoàn thiện dispatch() và _runMiddleware()
// ============================================================

namespace App\Core;

class Router
{
    /** @var array<int, array{method:string, path:string, handler:array, middleware:string[]}> */
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => 'GET',
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => 'POST',
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['method'], $route['path'], $method, $uri);
            if ($params !== null) {
                // Chạy middleware trước
                foreach ($route['middleware'] as $mw) {
                    $this->runMiddleware($mw);
                }
                // Gọi controller
                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass();
                $controller->$action(...$params);
                return;
            }
        }
        // TODO: render 404 view
        http_response_code(404);
        echo '<h1>404 — Không tìm thấy trang</h1>';
    }

    /**
     * So khớp route có {param} với URI thực tế.
     * Trả về array các params nếu khớp, null nếu không khớp.
     */
    private function matchRoute(string $routeMethod, string $routePath,
                                 string $reqMethod,  string $reqUri): ?array
    {
        if ($routeMethod !== $reqMethod) {
            return null;
        }
        // Chuyển {id} thành regex capture group
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $reqUri, $matches)) {
            array_shift($matches); // bỏ full match
            return $matches;
        }
        return null;
    }

    private function runMiddleware(string $name): void
    {
        // TODO (TV1 — Ngày 5): Đăng ký middleware theo tên
        // Hiện tại chỉ xử lý 'auth'
        if ($name === 'auth') {
            $mw = new \App\Middleware\AuthMiddleware();
            $mw->handle();
        }
    }
}
