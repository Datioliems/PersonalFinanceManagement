<?php
// ============================================================
// MIDDLEWARE — app/Middleware/AuthMiddleware.php
// ============================================================
// AUTH_ENABLED=true  → bảo vệ route, bắt đăng nhập
// AUTH_ENABLED=false → dùng Guest account (user_id thật trong DB)
//                      tránh lỗi FK khi tạo category/transaction
// ============================================================

namespace App\Middleware;

use App\Repositories\UserRepository;

class AuthMiddleware
{
    // ID của guest user trong DB (migration 005)
    const GUEST_USER_ID = 1;

    public function handle(): void
    {
        $authEnabled = filter_var(
            $_ENV['AUTH_ENABLED'] ?? 'true',
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$authEnabled) {
            $this->setGuestSession();
            return;
        }

        // AUTH_ENABLED=true — kiểm tra session
        if (!isset($_SESSION['user_id'])) {
            // Thử remember me cookie
            $auth = new \App\Services\AuthService(
                new UserRepository(),
                null
            );
            if ($auth->tryRememberLogin()) return;

            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Đặt session cho guest — dùng user_id thật trong DB.
     * Nếu guest chưa tồn tại → tự tạo (idempotent).
     */
    private function setGuestSession(): void
    {
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === self::GUEST_USER_ID) {
            return; // Đã set rồi
        }

        $repo = new UserRepository();

        // Tìm guest theo id cố định
        $guest = $repo->findById(self::GUEST_USER_ID);

        if (!$guest) {
            // Guest chưa có → tạo mới (migration chưa chạy hoặc bị xoá)
            $guestId = $repo->saveGuest([
                'username'       => 'guest',
                'email'          => 'guest@localhost',
                'password_hash'  => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'email_verified' => 1,
                'is_active'      => 1,
            ]);
        }

        $_SESSION['user_id']  = self::GUEST_USER_ID;
        $_SESSION['username'] = 'Guest';
    }
}
