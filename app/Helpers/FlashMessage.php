<?php
// ============================================================
// HELPER — app/Helpers/FlashMessage.php
// ============================================================
// Truyền thông báo qua redirect (PRG Pattern).
//
// Ví dụ: POST login thất bại → set flash → redirect GET /login
//         → GET /login đọc flash → hiện alert → unset flash
// ============================================================

namespace App\Helpers;

class FlashMessage
{
    /**
     * Lưu message vào session.
     * @param string $type  'success' | 'warning' | 'danger' | 'info'
     */
    public static function set(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Đọc và xoá message (gọi 1 lần trong View).
     */
    public static function get(string $type): ?string
    {
        $msg = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $msg;
    }

    /** Kiểm tra có message không (không xoá) */
    public static function has(string $type): bool
    {
        return !empty($_SESSION['_flash'][$type]);
    }

    /**
     * Render tất cả flash messages thành HTML Bootstrap alert.
     * Gọi một lần duy nhất trong layout.php.
     * Tự động unset tất cả sau khi render.
     */
    public static function renderAll(): string
    {
        if (empty($_SESSION['_flash'])) {
            return '';
        }

        $html = '';
        foreach ($_SESSION['_flash'] as $type => $msg) {
            $safe  = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
            $html .= <<<HTML
<div class="alert alert-{$type} alert-dismissible fade show" role="alert">
    {$safe}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
</div>
HTML;
        }
        unset($_SESSION['_flash']);
        return $html;
    }
}
