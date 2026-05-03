<?php
// ============================================================
// config/database.php
// ============================================================
// Đọc từ $_ENV (được load từ .env bởi index.php)
// Database::getInstance() require file này
// ============================================================

return [
    'host'    => $_ENV['DB_HOST']  ?? '127.0.0.1',
    'port'    => $_ENV['DB_PORT']  ?? '3306',
    'dbname'  => $_ENV['DB_NAME']  ?? 'de13_finance',
    'user'    => $_ENV['DB_USER']  ?? 'root',
    'pass'    => $_ENV['DB_PASS']  ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // lỗi SQL → throw Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetchAll() trả key=>value
        PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statement thật
    ],
];
