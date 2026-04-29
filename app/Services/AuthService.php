<?php
// ============================================================
// SERVICE — app/Services/AuthService.php
// ============================================================
// Business logic cho đăng nhập / đăng ký / đăng xuất.
// Không biết về HTTP request hay HTML — chỉ làm việc với data.
//
// Design:
//   interface Authenticatable  ← contract
//   class AuthService implements Authenticatable  ← implementation
//
// Dependency Injection: UserRepository được inject qua constructor.
// TV1 phụ trách — Ngày 2
// ============================================================

namespace App\Services;

use App\Repositories\UserRepository;

// ── Interface ─────────────────────────────────────────────────
interface Authenticatable
{
    public function login(string $username, string $password): bool;
    public function logout(): void;
    public function isLoggedIn(): bool;
    public function currentUserId(): ?int;
}

// ── Implementation ────────────────────────────────────────────
class AuthService implements Authenticatable
{
    public function __construct(
        private UserRepository $userRepo
    ) {}

    // ── login() ──────────────────────────────────────────────
    /**
     * Xác thực và tạo session.
     *
     * Luồng:
     *   1. Tìm user theo username
     *   2. Kiểm tra is_active
     *   3. password_verify()
     *   4. Tạo session
     *   5. Ghi log
     *
     * @return bool true nếu đăng nhập thành công
     */
    public function login(string $username, string $password): bool
    {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user = $this->userRepo->findByUsername($username);

        // User không tồn tại
        if (!$user) {
            $this->userRepo->logLogin(null, $username, $ip, 'failed');
            return false;
        }

        // Bỏ qua check is_active vì database không có cột này

        // Sai mật khẩu
        if (!password_verify($password, $user['password_hash'])) {
            $this->userRepo->logLogin((int)$user['id'], $username, $ip, 'failed');
            return false;
        }

        // Đăng nhập thành công — tạo session
        session_regenerate_id(true);  // chống Session Fixation Attack
        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['username'] = $user['username'];

        $this->userRepo->logLogin((int)$user['id'], $username, $ip, 'success');
        return true;
    }

    // ── register() ───────────────────────────────────────────
    /**
     * Đăng ký tài khoản mới.
     *
     * @param  string $username
     * @param  string $email
     * @param  string $password  plain text — sẽ được hash bên trong
     * @return int    ID của user vừa tạo
     * @throws \RuntimeException nếu username/email đã tồn tại
     */
    public function register(string $username, string $email, string $password): int
    {
        if ($this->userRepo->findByUsername($username)) {
            throw new \RuntimeException('Tên đăng nhập đã tồn tại.');
        }
        if ($this->userRepo->findByEmail($email)) {
            throw new \RuntimeException('Email này đã được dùng.');
        }

        return $this->userRepo->save([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    // ── logout() ─────────────────────────────────────────────
    public function logout(): void
    {
        // Xoá remember_token trong DB nếu có
        if (isset($_SESSION['user_id'])) {
            $this->userRepo->updateRememberToken(
                (int)$_SESSION['user_id'], null, null
            );
        }

        // Xoá session hoàn toàn
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        // Xoá remember cookie
        setcookie('remember_token', '', time() - 42000, '/', '', true, true);
    }

    // ── isLoggedIn() / currentUserId() ───────────────────────
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    // ── Remember Me ──────────────────────────────────────────
    /**
     * Tạo remember_token và set cookie 30 ngày.
     * Gọi sau khi login thành công nếu user tick "Nhớ mật khẩu".
     *
     * Bảo mật:
     *   - Token ngẫu nhiên 32 bytes → không đoán được
     *   - Hash SHA-256 trước khi lưu DB → nếu DB bị lộ vẫn an toàn
     *   - Cookie: Secure + HttpOnly
     */
    public function setRememberMeCookie(int $userId): void
    {
        $rawToken    = bin2hex(random_bytes(32));      // 64 chars hex
        $hashedToken = hash('sha256', $rawToken);       // hash trước khi lưu
        $expiresAt   = new \DateTime('+30 days');

        $this->userRepo->updateRememberToken($userId, $hashedToken, $expiresAt);

        setcookie(
            'remember_token',
            $rawToken,                                   // raw token trong cookie
            [
                'expires'  => time() + 30 * 86400,
                'path'     => '/',
                'secure'   => false,                     // đổi true khi deploy HTTPS
                'httponly' => true,                      // JS không đọc được cookie
                'samesite' => 'Lax',
            ]
        );
    }

    /**
     * Kiểm tra remember cookie và auto-login nếu hợp lệ.
     * Gọi trong index.php trước khi Router dispatch.
     */
    public function tryRememberLogin(): bool
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        if (empty($_COOKIE['remember_token'])) {
            return false;
        }

        $rawToken    = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $rawToken);
        $user        = $this->userRepo->findByRememberToken($hashedToken);

        if (!$user) {
            // Token không hợp lệ — xoá cookie
            setcookie('remember_token', '', time() - 42000, '/');
            return false;
        }

        // Auto-login
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
}
