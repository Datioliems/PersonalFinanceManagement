<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\{CsrfTokenManager, FlashMessage};

class AuthController extends BaseController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    // ── GET /login ─────────────────────────────────────────────
    public function showLogin(): void
    {
        if ($this->auth->isLoggedIn()) $this->redirect(BASE_URL . '/dashboard');
        $this->render('auth/login', ['csrf' => CsrfTokenManager::generate()]);
    }

    // ── POST /login ────────────────────────────────────────────
    public function login(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect(BASE_URL . '/login');
        }
        CsrfTokenManager::invalidate();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            FlashMessage::set('danger', 'Vui lòng nhập đầy đủ thông tin.');
            $this->redirect(BASE_URL . '/login');
        }

        try {
            if ($this->auth->login($username, $password)) {
                if (isset($_POST['remember_me'])) {
                    $this->auth->setRememberMeCookie($this->auth->currentUserId());
                }
                $intended = $_SESSION['_intended'] ?? BASE_URL . '/dashboard';
                unset($_SESSION['_intended']);
                $this->redirect($intended);
            }
            FlashMessage::set('danger', 'Tên đăng nhập không tồn tại.');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect(BASE_URL . '/login');
    }

    // ── GET /register ───────────────────────────────────────────
    public function showRegister(): void
    {
        if ($this->auth->isLoggedIn()) $this->redirect(BASE_URL . '/dashboard');
        $old         = $_SESSION['_register_old']   ?? [];
        $fieldErrors = $_SESSION['_register_errors'] ?? [];
        unset($_SESSION['_register_old'], $_SESSION['_register_errors']);
        $this->render('auth/register', [
            'csrf'        => CsrfTokenManager::generate(),
            'old'         => $old,
            'fieldErrors' => $fieldErrors,
        ]);
    }

    // ── POST /register ──────────────────────────────────────────
    public function register(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên hết hạn.');
            $this->redirect(BASE_URL . '/register');
        }
        CsrfTokenManager::invalidate();

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        // Validate per-field để highlight đúng ô bị lỗi
        $fieldErrors = [];
        if (strlen($username) < 3)                              $fieldErrors['username'][] = 'Tối thiểu 3 ký tự.';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username))       $fieldErrors['username'][] = 'Chỉ chứa a-z, 0-9 và dấu _.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $fieldErrors['email'][]    = 'Email không hợp lệ.';
        if (strlen($password) < 8)                              $fieldErrors['password'][] = 'Tối thiểu 8 ký tự.';
        if (!preg_match('/[A-Z]/', $password))                  $fieldErrors['password'][] = 'Cần ít nhất 1 chữ HOA.';
        if (!preg_match('/[0-9]/', $password))                  $fieldErrors['password'][] = 'Cần ít nhất 1 chữ số.';
        if ($password !== $confirm)                             $fieldErrors['confirm'][]  = 'Hai mật khẩu không khớp.';

        if (!empty($fieldErrors)) {
                $_SESSION['_register_old']    = [
                    'username'         => $username,
                    'email'            => $email,
                    'password'         => $password,
                    'password_confirm' => $confirm,
                ];
            $_SESSION['_register_errors'] = $fieldErrors;
            $this->redirect(BASE_URL . '/register');
        }

        try {
            $userId = $this->auth->register($username, $email, $password);

            // Không tự động đăng nhập — yêu cầu xác nhận email trước
            FlashMessage::set('success', '✅ Đăng ký thành công! Kiểm tra email để xác nhận tài khoản.');
            $_SESSION['_login_prefill_username'] = $username;
            $this->redirect(BASE_URL . '/login');
        } catch (\RuntimeException $e) {
            $_SESSION['_register_old'] = [
                'username'         => $username,
                'email'            => $email,
                'password'         => $password,
                'password_confirm' => $confirm,
            ];

            $message = $e->getMessage();
            $field   = str_contains($message, 'Email') ? 'email' : 'username';
            $_SESSION['_register_errors'] = [$field => [$message]];
            $this->redirect(BASE_URL . '/register');
        }
    }

    // ── GET /verify-email?token=... ────────────────────────────
    public function verifyEmail(): void
    {
        try {
            $this->auth->verifyEmail(trim($_GET['token'] ?? ''));
            FlashMessage::set('success', '✅ Email đã xác nhận! Bạn có thể đăng nhập.');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
        }
        $this->redirect(BASE_URL . '/login');
    }

    // ── GET /forgot-password ────────────────────────────────────
    public function showForgot(): void
    {
        $this->render('auth/forgot', ['csrf' => CsrfTokenManager::generate()]);
    }

    // ── POST /forgot-password ───────────────────────────────────
    public function forgotPassword(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            $this->redirect(BASE_URL . '/forgot-password');
        }
        CsrfTokenManager::invalidate();

        $this->auth->forgotPassword(trim($_POST['email'] ?? ''));
        FlashMessage::set('info', 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu. Kiểm tra cả thư mục Spam.');
        $this->redirect(BASE_URL . '/login');
    }

    // ── GET /reset-password?token=... ──────────────────────────
    public function showReset(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (!$token) { FlashMessage::set('danger', 'Link không hợp lệ.'); $this->redirect(BASE_URL . '/login'); }
        $this->render('auth/reset', ['csrf' => CsrfTokenManager::generate(), 'token' => $token]);
    }

    // ── POST /reset-password ────────────────────────────────────
    public function resetPassword(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            $this->redirect(BASE_URL . '/login');
        }
        CsrfTokenManager::invalidate();

        $pw  = $_POST['password']         ?? '';
        $pw2 = $_POST['password_confirm'] ?? '';
        $tok = trim($_POST['token']        ?? '');

        if (strlen($pw) < 8) { FlashMessage::set('danger', 'Mật khẩu phải có ít nhất 8 ký tự.'); $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($tok)); }
        if ($pw !== $pw2)    { FlashMessage::set('danger', 'Hai mật khẩu không khớp.');             $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($tok)); }

        try {
            $this->auth->resetPassword($tok, $pw);
            FlashMessage::set('success', '✅ Mật khẩu đã được đổi. Bạn có thể đăng nhập.');
            $this->redirect(BASE_URL . '/login');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
            $this->redirect(BASE_URL . '/login');
        }
    }

    // ── GET /logout ─────────────────────────────────────────────
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect(BASE_URL . '/login');
    }

    private function validateRegisterInput(string $u, string $e, string $p, string $pc): array
    {
        $err = [];
        if (strlen($u) < 3)                              $err[] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $u))       $err[] = 'Tên đăng nhập chỉ chứa a-z, 0-9 và dấu gạch dưới.';
        if (!filter_var($e, FILTER_VALIDATE_EMAIL))      $err[] = 'Email không hợp lệ.';
        if (strlen($p) < 8)                              $err[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        if (!preg_match('/[A-Z]/', $p))                  $err[] = 'Mật khẩu cần ít nhất 1 chữ HOA.';
        if (!preg_match('/[0-9]/', $p))                  $err[] = 'Mật khẩu cần ít nhất 1 chữ số.';
        if ($p !== $pc)                                  $err[] = 'Hai mật khẩu không khớp.';
        return $err;
    }
}
