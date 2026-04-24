<?php
// ============================================================
// FLASH MESSAGE — app/Helpers/FlashMessage.php
// ============================================================
// Dùng để truyền thông báo qua redirect (PRG pattern)
// ============================================================

namespace App\Helpers;

class FlashMessage
{
    /** Lưu message vào session */
    public static function set(string $type, string $message): void
    {
        // $type: 'success' | 'warning' | 'danger' | 'info'
        $_SESSION['_flash'][$type] = $message;
    }

    /** Đọc và xoá message (gọi 1 lần trong View) */
    public static function get(string $type): ?string
    {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }

    /** Kiểm tra có message không (không xoá) */
    public static function has(string $type): bool
    {
        return !empty($_SESSION['_flash'][$type]);
    }

    /** Dùng trong View: hiển thị tất cả flash messages */
    public static function renderAll(): string
    {
        if (empty($_SESSION['_flash'])) return '';
        $html = '';
        foreach ($_SESSION['_flash'] as $type => $msg) {
            $safe = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
            $html .= "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">"
                   . $safe
                   . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
                   . '</div>';
        }
        unset($_SESSION['_flash']);
        return $html;
    }
}
