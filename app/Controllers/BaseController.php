<?php
// ============================================================
// BASE CONTROLLER — app/Controllers/BaseController.php
// ============================================================

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Render một View file, truyền data vào View qua extract().
     *
     * @param string $view  Đường dẫn tương đối từ app/Views, VD: 'auth/login'
     * @param array  $data  ['key' => value] — có thể dùng $key trong View
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View không tồn tại: {$view}");
        }
        require $viewFile;
    }

    /** Redirect đến URL khác */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** Lấy user_id hiện tại từ session */
    protected function currentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    /** Lấy tham số GET đã sanitize */
    protected function getQuery(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? htmlspecialchars($_GET[$key], ENT_QUOTES) : $default;
    }
}
