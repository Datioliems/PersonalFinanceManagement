<?php
// ============================================================
// CORE — app/Core/Router.php
// ============================================================
// Front Controller Pattern: nhận method+URI → tìm route
// → chạy middleware → gọi Controller::method()
// ============================================================

namespace App\Core;

class Router
{
    private array $routes = [];

    public function __construct(private Container $container)
    {
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->routes[] = ['GET', $path, $handler, $middleware];
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->routes[] = ['POST', $path, $handler, $middleware];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Bỏ query string khỏi URI
        $uri = strtok($uri, '?');

        foreach ($this->routes as [$routeMethod, $routePath, $handler, $middleware]) {
            $params = $this->match($routeMethod, $routePath, $method, $uri);
            if ($params === null) {
                continue;
            }
            // Chạy middleware trước
            foreach ($middleware as $mw) {
                $this->runMiddleware($mw);
            }
            // Gọi Controller thông qua container
            [$class, $action] = $handler;
            $controller = $this->container->make($class);
            $controller->$action(...$params);
            return;
        }

        // 404
        http_response_code(404);
        echo '<h1 style="font-family:sans-serif;padding:2rem">404 — Không tìm thấy trang</h1>';
    }

    /**
     * Khớp route có tham số {id}.
     * Trả về array params nếu khớp, null nếu không.
     */
    private function match(string $rm, string $rp, string $m, string $u): ?array
    {
        if ($rm !== $m) {
            return null;
        }
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $rp);
        if (preg_match('#^' . $pattern . '$#', $u, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return null;
    }

    private function runMiddleware(string $name): void
    {
        if ($name === 'auth') {
            (new \App\Middleware\AuthMiddleware())->handle();
        }
    }
}
