<?php
// ============================================================
// DATABASE CONFIG — config/database.php
// ============================================================
// Đọc thông tin kết nối từ .env.
// Dùng bởi App\Core\Database (Singleton PDO).
// ============================================================

return [
    'host'    => $_ENV['DB_HOST']   ?? '127.0.0.1',
    'port'    => $_ENV['DB_PORT']   ?? '3306',
    'dbname'  => $_ENV['DB_NAME']   ?? 'de13_finance',
    'user'    => $_ENV['DB_USER']   ?? 'root',
    'pass'    => $_ENV['DB_PASS']   ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
