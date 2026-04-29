<?php
// ============================================================
// CORE — app/Core/Database.php
// ============================================================
// Singleton Pattern: chỉ tạo 1 kết nối PDO duy nhất/request.
//
// Cách dùng:
//   $pdo = Database::getInstance();
//   $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
// ============================================================

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}  // cấm new Database()
    private function __clone()     {}  // cấm clone

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require BASE_PATH . '/config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'], $cfg['port'], $cfg['dbname'], $cfg['charset']
            );
            try {
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
            } catch (PDOException $e) {
                // Không expose thông tin DB ra browser
                error_log('[DB] Connection error: ' . $e->getMessage());
                throw new \RuntimeException('Không thể kết nối cơ sở dữ liệu.');
            }
        }
        return self::$instance;
    }

    /** Reset instance (dùng cho unit test) */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
