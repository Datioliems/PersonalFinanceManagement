<?php
// ============================================================
// CSRF TOKEN MANAGER — app/Helpers/CsrfTokenManager.php
// ============================================================
// TODO (TV5 — Ngày 2): generate() và validate()
// ============================================================

namespace App\Helpers;

class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_token';

    /** Tạo token mới và lưu vào session */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    /** Kiểm tra token từ form có khớp session không */
    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /** Xoá token sau khi validate (one-time use) */
    public static function invalidate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
