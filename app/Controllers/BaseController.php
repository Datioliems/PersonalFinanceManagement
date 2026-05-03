<?php
// ============================================================
// CONTROLLER — app/Controllers/BaseController.php
// ============================================================
// Class cha cho tất cả Controller.
// Cung cấp: render(), redirect(), currentUserId()
// ============================================================

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Render View file, inject data qua extract().
     *
     * @param string $view  VD: 'auth/login' → app/Views/auth/login.php
     * @param array  $data  Biến được unpack vào scope của View
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View không tồn tại: {$view}");
        }
        require $viewFile;
    }

    protected function redirect(string $url): never
    {
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';

        // Nếu URL đã chứa BASE_URL ở đầu, ta không cần thêm nữa
        if (BASE_URL !== '' && str_starts_with($url, BASE_URL)) {
            // Không làm gì, URL đã chuẩn
        } elseif ($basePath !== '' && str_starts_with($url, $basePath)) {
            // Nếu URL bắt đầu bằng đường dẫn tương đối của project, thay thế bằng BASE_URL tuyệt đối
            $url = BASE_URL . substr($url, strlen($basePath));
        } elseif (str_starts_with($url, '/')) {
            $url = BASE_URL . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /** Lấy user_id hiện tại từ session */
    protected function currentUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }
}
