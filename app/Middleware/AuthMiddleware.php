<?php
// ============================================================
// MIDDLEWARE — app/Middleware/AuthMiddleware.php
// ============================================================
// Chạy trước Controller cho tất cả route yêu cầu đăng nhập.
// Router gọi handle() khi route có middleware = ['auth'].
//
// Luồng:
//   Request → Router → AuthMiddleware::handle()
//     → Chưa login? → Lưu _intended → redirect /login
//     → Đã login?   → Tiếp tục, Controller chạy bình thường
// ============================================================

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Kiểm tra session.
     * Nếu chưa đăng nhập: lưu URL muốn vào rồi redirect /login.
     * Nếu đã đăng nhập: không làm gì, Controller tiếp tục.
     */
    public function handle(): void
    {
        if (!isset($_SESSION['user_id'])) {
            // Lưu URL để sau khi login redirect đúng chỗ
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
}
