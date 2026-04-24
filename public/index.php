<?php
// ============================================================
// FRONT CONTROLLER — public/index.php
// ============================================================
// Đây là entry point duy nhất của toàn bộ ứng dụng.
// Mọi request đều đi qua file này trước.
//
// Luồng: Request → index.php → Router → Middleware → Controller → View
// ============================================================

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// 1. Autoload thủ công (không dùng Composer)
require BASE_PATH . '/autoload.php';

// 2. Load biến môi trường từ .env
$dotenvFile = BASE_PATH . '/.env';
if (file_exists($dotenvFile)) {
    $lines = file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// 3. Khởi động session
session_name($_ENV['SESSION_NAME'] ?? 'de13_session');
session_start();

// 4. Load Router và đăng ký routes
$router = new \App\Core\Router();
require BASE_PATH . '/routes.php';

// 5. Dispatch request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
