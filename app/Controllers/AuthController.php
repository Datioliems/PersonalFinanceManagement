<?php
// ============================================================
// CONTROLLER — app/Controllers/AuthController.php
// ============================================================
// Nhận HTTP request → gọi AuthService → redirect/render.
// Controller KHÔNG chứa business logic (password_verify, hash...).
// Mọi logic nằm trong AuthService.
//
// Routes:
//   GET  /project/de13_complete/public/project/de13_complete/public/login     → showLogin()
//   POST /project/de13_complete/public/project/de13_complete/public/login     → login()
//   GET  /register  → showRegister()
//   POST /register  → register()
//   GET  /logout    → logout()
//
// TV1 phụ trách — Ngày 3
// ============================================================

namespace App\Controllers;

use App\Services\AuthService;
use App\Repositories\UserRepository;
use App\Helpers\CsrfTokenManager;
use App\Helpers\FlashMessage;

class AuthController extends BaseController
{
    private AuthService $auth;

    public function __construct()
    {
        // Dependency Injection thủ công
        $this->auth = new AuthService(new UserRepository());
    }

    // ── GET /project/de13_complete/public/project/de13_complete/public/login ────────────────────────────────────────────
    public function showLogin(): void
    {
        // Nếu đã đăng nhập rồi → về dashboard
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/project/de13_complete/public/dashboard');
        }

        $csrf = CsrfTokenManager::generate();
        $this->render('auth/login', ['csrf' => $csrf]);
    }

    // ── POST /project/de13_complete/public/login ───────────────────────────────────────────
    public function login(): void
    {
        // 1. Validate CSRF — luôn là bước ĐẦU TIÊN
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('/project/de13_complete/public/login');
        }
        CsrfTokenManager::invalidate();

        $username   = trim($_POST['username'] ?? '');
        $password   = $_POST['password']   ?? '';
        $rememberMe = isset($_POST['remember_me']);

        // 2. Validate input cơ bản
        if (empty($username) || empty($password)) {
            FlashMessage::set('danger', 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
            $this->redirect('/project/de13_complete/public/login');
        }

        // 3. Gọi AuthService
        if ($this->auth->login($username, $password)) {
            // 4. Remember Me
            if ($rememberMe) {
                $this->auth->setRememberMeCookie($this->auth->currentUserId());
            }

            // 5. Redirect về URL user muốn vào ban đầu (hoặc dashboard)
            $intended = $_SESSION['_intended'] ?? '/project/de13_complete/public/dashboard';
            unset($_SESSION['_intended']);
            $this->redirect($intended);
        }

        // Login thất bại
        FlashMessage::set('danger', 'Tên đăng nhập hoặc mật khẩu không đúng.');
        $this->redirect('/project/de13_complete/public/login');
    }

    // ── GET /register ─────────────────────────────────────────
    public function showRegister(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/project/de13_complete/public/dashboard');
        }

        $csrf = CsrfTokenManager::generate();
        $this->render('auth/register', ['csrf' => $csrf]);
    }

    // ── POST /register ────────────────────────────────────────
    public function register(): void
    {
        // 1. Validate CSRF
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            FlashMessage::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('/project/de13_complete/public/register');
        }
        CsrfTokenManager::invalidate();

        $username  = trim($_POST['username']         ?? '');
        $email     = trim($_POST['email']            ?? '');
        $password  = $_POST['password']              ?? '';
        $passwordC = $_POST['password_confirm']      ?? '';

        // 2. Validate input
        $errors = $this->validateRegisterInput($username, $email, $password, $passwordC);
        if (!empty($errors)) {
            FlashMessage::set('danger', implode('<br>', $errors));
            $this->redirect('/project/de13_complete/public/register');
        }

        // 3. Gọi AuthService
        try {
            $this->auth->register($username, $email, $password);
            FlashMessage::set('success', 'Đăng ký thành công! Bạn có thể đăng nhập ngay.');
            $this->redirect('/project/de13_complete/public/login');
        } catch (\RuntimeException $e) {
            FlashMessage::set('danger', $e->getMessage());
            $this->redirect('/project/de13_complete/public/register');
        }
    }

    // ── GET /logout ───────────────────────────────────────────
    public function logout(): void
    {
        $this->auth->logout();
        FlashMessage::set('info', 'Bạn đã đăng xuất thành công.');
        $this->redirect('/project/de13_complete/public/login');
    }

    // ── Private helpers ───────────────────────────────────────

    /**
     * Validate input đăng ký.
     * @return string[] Danh sách lỗi (rỗng = hợp lệ)
     */
    private function validateRegisterInput(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm
    ): array {
        $errors = [];

        if (strlen($username) < 3) {
            $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Hai mật khẩu không khớp nhau.';
        }

        return $errors;
    }
}
