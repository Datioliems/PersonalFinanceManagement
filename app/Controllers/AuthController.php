<?php
// ============================================================
// AUTH CONTROLLER — app/Controllers/AuthController.php
// ============================================================
// TV1 — Ngày 3
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
        $this->auth = new AuthService(new UserRepository());
    }

    /** GET /login */
    public function showLogin(): void
    {
        $csrf = CsrfTokenManager::generate();
        $this->render('auth/login', ['csrf' => $csrf]);
    }

    /**
     * POST /login
     * TODO: validate CSRF → auth->login() → redirect /dashboard
     *       Nếu sai: FlashMessage::set('danger', 'Sai tài khoản/mật khẩu')
     */
    public function login(): void
    {
        // TODO
        if (!CsrfTokenManager::validate($_POST['csrf_token'] ?? '')) {
            $this->redirect('/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->auth->login($username, $password)) {
            $intended = $_SESSION['_intended'] ?? '/dashboard';
            unset($_SESSION['_intended']);
            $this->redirect($intended);
        } else {
            FlashMessage::set('danger', 'Tên đăng nhập hoặc mật khẩu không đúng.');
            $this->redirect('/login');
        }
    }

    /** GET /register */
    public function showRegister(): void
    {
        $csrf = CsrfTokenManager::generate();
        $this->render('auth/register', ['csrf' => $csrf]);
    }

    /**
     * POST /register
     * TODO: validate input → auth->register() → redirect /login
     */
    public function register(): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV1 Ngày 3');
    }

    /** GET /logout */
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/login');
    }
}
