<?php
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
        private UserRepository $userRepo,
        private ?MailService   $mailer = null
    ) {}

    /**
     * @throws \RuntimeException với message hiển thị cho user
     */
    public function login(string $username, string $password): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Chặn brute-force theo IP (>30 lần trong 15 phút)
        if ($this->userRepo->countRecentFailsByIp($ip) >= 30) {
            throw new \RuntimeException('Quá nhiều lần thử từ IP này. Thử lại sau 15 phút.');
        }

        $user = $this->userRepo->findByUsername($username);
        if (!$user) {
            $this->userRepo->logLogin(null, $username, $ip, 'failed');
            return false;
        }

        $uid = (int)$user['id'];

        // Kiểm tra khoá tạm thời (5 lần sai)
        $locked = $this->userRepo->getLockedSeconds($uid);
        if ($locked > 0) {
            throw new \RuntimeException("Tài khoản bị khoá tạm thời. Thử lại sau {$locked} giây.");
        }

        if (!($user['is_active'] ?? 1)) {
            $this->userRepo->logLogin($uid, $username, $ip, 'failed');
            throw new \RuntimeException('Tài khoản đã bị khoá.');
        }

        if (!($user['email_verified'] ?? 0)) {
            $this->userRepo->logLogin($uid, $username, $ip, 'failed');
            throw new \RuntimeException('Email chưa được xác nhận. Kiểm tra hộp thư và bấm link xác nhận.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->userRepo->logLogin($uid, $username, $ip, 'failed');
            $this->userRepo->incrementFailedAttempts($uid);
            $remaining = max(0, 4 - (int)($user['login_attempts'] ?? 0));
            if ($remaining > 0) {
                throw new \RuntimeException("Sai mật khẩu. Còn {$remaining} lần thử.");
            }
            throw new \RuntimeException('Sai mật khẩu. Tài khoản bị khoá 60 giây.');
        }

        $this->userRepo->resetFailedAttempts($uid);
        session_regenerate_id(true);
        $_SESSION['user_id']  = $uid;
        $_SESSION['username'] = $user['username'];
        $this->userRepo->logLogin($uid, $username, $ip, 'success');
        return true;
    }

    /** @throws \RuntimeException */
    public function register(string $username, string $email, string $password): int
    {
        $username = htmlspecialchars(strip_tags(trim($username)), ENT_QUOTES, 'UTF-8');
        $email    = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        // Username đã tồn tại và đã verify → báo lỗi
        $existingByUsername = $this->userRepo->findByUsername($username);
        if ($existingByUsername && $existingByUsername['email_verified']) {
            throw new \RuntimeException('Tên đăng nhập đã tồn tại.');
        }
        // Username tồn tại nhưng chưa verify → xoá để đăng ký lại
        if ($existingByUsername && !$existingByUsername['email_verified']) {
            $this->userRepo->deleteById((int)$existingByUsername['id']);
        }

        // Email đã tồn tại và đã verify → báo lỗi
        $existingByEmail = $this->userRepo->findByEmail($email);
        if ($existingByEmail && $existingByEmail['email_verified']) {
            throw new \RuntimeException('Email này đã được dùng.');
        }
        // Email tồn tại nhưng chưa verify → xoá để đăng ký lại
        if ($existingByEmail && !$existingByEmail['email_verified']) {
            $this->userRepo->deleteById((int)$existingByEmail['id']);
        }

        $verifyToken = bin2hex(random_bytes(32));
        $userId = $this->userRepo->save([
            'username'           => $username,
            'email'              => $email,
            'password_hash'      => password_hash($password, PASSWORD_BCRYPT),
            'email_verify_token' => $verifyToken,
            'email_verified'     => 0, // Chưa xác nhận — cần bấm link trong mail
        ]);

        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $this->mailer?->sendVerification($email, $username, "{$appUrl}/verify-email?token={$verifyToken}");
        return $userId;
    }

    /** @throws \RuntimeException */
    public function verifyEmail(string $token): void
    {
        $user = $this->userRepo->findByEmailVerifyToken($token);
        if (!$user) throw new \RuntimeException('Link xác nhận không hợp lệ hoặc đã hết hạn.');
        $this->userRepo->markEmailVerified((int)$user['id']);
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userRepo->findByEmail($email);
        // Không tiết lộ email có tồn tại không
        // Nhưng chặn reset nếu chưa verify — gửi lại email verify thay thế
        if (!$user) return;

        if (!($user['email_verified'] ?? 0)) {
            // Gửi lại email xác nhận thay vì email reset
            $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
            $this->mailer?->sendVerification(
                $user['email'],
                $user['username'],
                "{$appUrl}/verify-email?token={$user['email_verify_token']}"
            );
            return; // Không tạo reset token
        }

        $raw  = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $this->userRepo->savePasswordResetToken((int)$user['id'], $hash);

        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $this->mailer?->sendPasswordReset($user['email'], $user['username'], "{$appUrl}/reset-password?token={$raw}");
    }

    /** @throws \RuntimeException */
    public function resetPassword(string $rawToken, string $newPassword): void
    {
        $hash = hash('sha256', $rawToken);
        $row  = $this->userRepo->findValidResetToken($hash);
        if (!$row) throw new \RuntimeException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn (30 phút).');

        $this->userRepo->updatePassword((int)$row['user_id'], password_hash($newPassword, PASSWORD_BCRYPT));
        $this->userRepo->markResetTokenUsed($hash);
        $this->userRepo->resetFailedAttempts((int)$row['user_id']);
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->userRepo->updateRememberToken((int)$_SESSION['user_id'], null, null);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        setcookie('remember_token', '', time() - 42000, '/');
    }

    public function isLoggedIn(): bool    { return isset($_SESSION['user_id']); }
    public function currentUserId(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }

    public function setRememberMeCookie(int $userId): void
    {
        $raw  = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $this->userRepo->updateRememberToken($userId, $hash, new \DateTime('+30 days'));
        setcookie('remember_token', $raw, [
            'expires' => time() + 30 * 86400, 'path' => '/',
            'secure' => false, 'httponly' => true, 'samesite' => 'Lax',
        ]);
    }

    public function tryRememberLogin(): bool
    {
        if ($this->isLoggedIn() || empty($_COOKIE['remember_token'])) return false;
        $user = $this->userRepo->findByRememberToken(hash('sha256', $_COOKIE['remember_token']));
        if (!$user) { setcookie('remember_token', '', time() - 42000, '/'); return false; }
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
}
