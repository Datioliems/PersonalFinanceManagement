<?php
// ============================================================
// FRONT CONTROLLER — public/index.php
// ============================================================
// Entry point duy nhất. Mọi request đi qua đây.
//
// Luồng:
//   Request → .htaccess rewrite → index.php
//     1. Define BASE_PATH
//     2. Autoload (PSR-4 thủ công)
//     3. Load .env
//     4. Start session
//     5. Auto-login (Remember Me cookie)
//     6. Load routes
//     7. Router::dispatch()
// ============================================================

declare(strict_types=1);

// Thiết lập múi giờ mặc định toàn hệ thống là Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

define('BASE_PATH', dirname(__DIR__));

// 1. Autoload
require BASE_PATH . '/autoload.php';

// 1.5. Container IoC đơn giản
$container = new \App\Core\Container();
$container->bind(\App\Repositories\BudgetRepositoryInterface::class, \App\Repositories\BudgetRepository::class);

// 2. Load .env
$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

// 3. Session
session_name($_ENV['SESSION_NAME'] ?? 'de13_session');
session_start();

// 4. Auto-login qua Remember Me cookie (TV1 Ngày 6)
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    $authService = $container->make(\App\Services\AuthService::class);
    $authService->tryRememberLogin();
}

// 5. Router + routes
$router = new \App\Core\Router($container);
require BASE_PATH . '/routes.php';

// 6. Dispatch
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Xử lý path khi chạy trong thư mục con (XAMPP htdocs)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$scriptName = str_replace('\\', '/', $scriptName);
if ($scriptName !== '' && !str_starts_with($scriptName, '/')) {
    $scriptName = '/' . $scriptName;
}
if ($scriptName === '/') {
    $scriptName = '';
}

// Xác định thư mục gốc của project (bỏ /public)
$projectDir = dirname($scriptName);
$projectDir = str_replace('\\', '/', $projectDir);
if ($projectDir !== '' && !str_starts_with($projectDir, '/')) {
    $projectDir = '/' . $projectDir;
}
if ($projectDir === '/') {
    $projectDir = '';
}

// Cố định BASE_URL thành đường dẫn tuyệt đối đầy đủ theo yêu cầu
define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');

if ($scriptName !== '' && str_starts_with($uri, $scriptName)) {
    $uri = substr($uri, strlen($scriptName));
} elseif ($projectDir !== '' && str_starts_with($uri, $projectDir)) {
    $uri = substr($uri, strlen($projectDir));
}

// Bỏ phần /public nếu nó vô tình dính vào (ví dụ /public/login)
if (str_starts_with($uri, '/public')) {
    $uri = substr($uri, 7);
}

if ($uri === '') {
    $uri = '/';
}

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $uri
);
