<?php
// ============================================================
// DATABASE — app/Core/Database.php
// ============================================================
// Singleton Pattern: đảm bảo chỉ tạo 1 kết nối PDO duy nhất.
//
// Cách dùng:
//   $db = Database::getInstance();
//   $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
//   $stmt->execute([$id]);
//
// TODO (TV3 — Ngày 1): Implement getInstance() và connect()
// ============================================================

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // Constructor private: ngăn new Database() từ bên ngoài
    private function __construct() {}

    // Clone private: ngăn clone object
    private function __clone() {}

    /**
     * Trả về PDO instance duy nhất (tạo mới nếu chưa có).
     *
     * TODO: Đọc config từ config/database.php
     *       Tạo DSN string: "mysql:host=...;port=...;dbname=...;charset=..."
     *       new PDO($dsn, $user, $pass, $options)
     *       Bắt PDOException và throw RuntimeException với message rõ ràng
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // TODO: điền code vào đây
            $config = require BASE_PATH . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['user'],
                    $config['pass'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Không expose thông tin DB ra màn hình ở production
                error_log('DB Connection Error: ' . $e->getMessage());
                throw new \RuntimeException('Không thể kết nối database. Xem log để biết thêm.');
            }
        }

        return self::$instance;
    }

    /**
     * Reset instance (dùng cho testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
