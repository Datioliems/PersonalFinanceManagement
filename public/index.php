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
    $authService = new \App\Services\AuthService(
        new \App\Repositories\UserRepository()
    );
    $authService->tryRememberLogin();
}

// 5. Router + routes
$router = new \App\Core\Router();
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
$projectDir = $scriptName === '' ? '' : dirname($scriptName);
$projectDir = str_replace('\\', '/', $projectDir);
if ($projectDir !== '' && !str_starts_with($projectDir, '/')) {
    $projectDir = '/' . $projectDir;
}
if ($projectDir === '/' || $projectDir === '.') {
    $projectDir = '';
}

// Ưu tiên APP_URL khi host/port khớp request hiện tại, nếu không thì tự động suy ra
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$authority = $_SERVER['HTTP_HOST'] ?? 'localhost';
$runtimeBaseUrl = $scheme . '://' . $authority . $scriptName;

$configuredBaseUrl = trim($_ENV['APP_URL'] ?? '');
$configuredAuthority = parse_url($configuredBaseUrl, PHP_URL_HOST);
$configuredPort = parse_url($configuredBaseUrl, PHP_URL_PORT);
if ($configuredAuthority) {
    $configuredAuthority .= $configuredPort ? ':' . $configuredPort : '';
}

if ($configuredBaseUrl !== '' && $configuredAuthority !== null && strcasecmp($configuredAuthority, $authority) === 0) {
    define('BASE_URL', rtrim($configuredBaseUrl, '/'));
} else {
    define('BASE_URL', rtrim($runtimeBaseUrl, '/'));
}

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
