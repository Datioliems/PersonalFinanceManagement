<?php
// ============================================================
// AUTH MIDDLEWARE — app/Middleware/AuthMiddleware.php
// ============================================================
// TODO (TV1 — Ngày 5): Kiểm tra session trước khi vào route
// ============================================================

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Nếu chưa đăng nhập → redirect về /login.
     * Nếu đã đăng nhập → tiếp tục (không làm gì).
     *
     * TODO: Kiểm tra $_SESSION['user_id']
     *       Gọi session_regenerate_id() theo định kỳ để chống session fixation
     */
    public function handle(): void
    {
        if (!isset($_SESSION['user_id'])) {
            // Lưu URL người dùng đang muốn vào để redirect sau đăng nhập
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
}
