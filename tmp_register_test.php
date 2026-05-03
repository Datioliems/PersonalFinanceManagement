<?php

define('BASE_PATH', __DIR__);
require BASE_PATH . '/autoload.php';

$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

$repo = new \App\Repositories\UserRepository();
$auth = new \App\Services\AuthService($repo, new \App\Services\MailService());

$u = 'tmpuser_' . time();
$e = 'tmpuser_' . time() . '@gmail.com';
try {
    $id = $auth->register($u, $e, 'Aa12345678');
    echo "REGISTER_OK id={$id}\n";
} catch (Throwable $ex) {
    echo "REGISTER_ERR: " . $ex->getMessage() . "\n";
}
