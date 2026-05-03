<?php
// ============================================================
// MIDDLEWARE — app/Middleware/AuthMiddleware.php
// ============================================================
// Hỗ trợ bật/tắt Auth qua .env:
//   AUTH_ENABLED=true   → bảo vệ route như bình thường
//   AUTH_ENABLED=false  → bỏ qua auth (scope không có đăng nhập)
// ============================================================

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        // Đọc flag từ .env — mặc định true (có auth)
        $authEnabled = filter_var(
            $_ENV['AUTH_ENABLED'] ?? 'true',
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$authEnabled) {
            // Checkpoint C / scope không auth — đặt user_id giả để
            // các Controller gọi currentUserId() không bị null
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['user_id']  = 0;
                $_SESSION['username'] = 'Guest';
            }
            return; // Không chặn, tiếp tục vào Controller
        }

        // AUTH_ENABLED=true — kiểm tra session như bình thường
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}