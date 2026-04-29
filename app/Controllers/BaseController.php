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

    /** Redirect và dừng lại (PRG Pattern) */
    protected function redirect(string $url): never
    {
        if (str_starts_with($url, '/')) {
            // Tránh nối double prefix nếu user đã gõ sẵn
            $url = str_replace('/project/de13_complete/public', '', $url);
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
