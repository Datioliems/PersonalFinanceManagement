<?php
// ============================================================
// AUTH SERVICE — app/Services/AuthService.php
// ============================================================
// TODO (TV5 — Ngày 2): Implement login(), register()
// ============================================================

namespace App\Services;

use App\Repositories\UserRepository;

interface Authenticatable
{
    public function login(string $username, string $password): bool;
    public function logout(): void;
    public function isLoggedIn(): bool;
    public function currentUserId(): ?int;
}

class AuthService implements Authenticatable
{
    public function __construct(
        private UserRepository $userRepo
    ) {}

    /**
     * Đăng nhập: tìm user → password_verify() → lưu session.
     *
     * TODO: findByUsername → password_verify → session_regenerate_id(true) → $_SESSION
     * @return bool true nếu đăng nhập thành công
     */
    public function login(string $username, string $password): bool
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV5 Ngày 2');
    }

    /**
     * Đăng ký user mới.
     * TODO: check trùng username/email → password_hash() → userRepo->save()
     * @throws \RuntimeException nếu username/email đã tồn tại
     */
    public function register(string $username, string $email, string $password): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV5 Ngày 2');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }
}
